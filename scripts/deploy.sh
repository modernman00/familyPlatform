#!/bin/bash

# ===============================================================================
# DEPLOYMENT SCRIPT (Refactored – Declarative per‑app pipelines)
# Architect: James
# ===============================================================================

set -e

# ---------------------------------------------------------------------------
# Helper functions
# ---------------------------------------------------------------------------
log_success() { echo -e "\e[32m✔ $1\e[0m"; }
log_error()   { echo -e "\e[31m✖ $1\e[0m"; }
log_info()    { echo -e "\e[34mℹ $1\e[0m"; }

# Parse command‑line flags
DRY_RUN=false
FORCE=false
CI_MODE=false
while [[ $# -gt 0 ]]; do
  case $1 in
    --dry-run) DRY_RUN=true; shift ;;
    --force)   FORCE=true;  shift ;;
    --ci)      CI_MODE=true; shift ;;
    *) echo "Unknown option: $1"; exit 1 ;;
  esac
done

# ---------------------------------------------------------------------------
# Load manifest
# ---------------------------------------------------------------------------
if ! command -v yq >/dev/null 2>&1; then
  log_error "yq not found – please install yq to parse deployments.yml"
  exit 1
fi

MANIFEST="$(pwd)/deployments.yml"
if [[ ! -f "$MANIFEST" ]]; then
  log_error "Manifest file deployments.yml not found at $MANIFEST"
  exit 1
fi

# ---------------------------------------------------------------------------
# Load .env so the required-vars check below sees APP_URL/DB_HOST/etc.
# Reads the file directly (no process substitution / subshell sourcing,
# which isn't reliable in every execution environment) and normalizes
# "KEY = value" to "KEY=value", since this .env mixes both styles and
# bash assignment doesn't allow spaces around "=". Lines whose key isn't
# a valid identifier (e.g. "401URL=...") are skipped.
# ---------------------------------------------------------------------------
if [[ -f "$(pwd)/.env" ]]; then
  set -a
  while IFS= read -r line || [[ -n "$line" ]]; do
    [[ "$line" =~ ^[[:space:]]*(#.*)?$ ]] && continue
    if [[ "$line" =~ ^[[:space:]]*([A-Za-z_][A-Za-z0-9_]*)[[:space:]]*=[[:space:]]*(.*)$ ]]; then
      export "${BASH_REMATCH[1]}=${BASH_REMATCH[2]}"
    fi
  done < "$(pwd)/.env"
  set +a
fi

# Gather git information once
GIT_SHA=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
DEPLOYER=$(whoami)

# Prepare final JSON report
REPORT_FILE="deploy_report.json"
REPORT="{\"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\", \"git_sha\": \"$GIT_SHA\", \"deployer\": \"$DEPLOYER\", \"apps\": []}"

# ---------------------------------------------------------------------------
# Iterate over apps defined in the manifest
# ---------------------------------------------------------------------------
APP_COUNT=$(yq eval '.apps | length' "$MANIFEST")
log_info "Found $APP_COUNT apps in deployments.yml"

INDEX=0
while [[ $INDEX -lt $APP_COUNT ]]; do
  APP_NAME=$(yq eval ".apps | keys | .[$INDEX]" "$MANIFEST")
  log_info "Processing app: $APP_NAME"

  # Extract fields
  BUILD_CMD=$(yq eval ".apps.$APP_NAME.build" "$MANIFEST")
  MIGRATE_CMD=$(yq eval ".apps.$APP_NAME.migrate" "$MANIFEST")
  HEALTH_CMD=$(yq eval ".apps.$APP_NAME.healthcheck" "$MANIFEST")
  ENV_VARS=$(yq eval ".apps.$APP_NAME.env[]" "$MANIFEST" | tr '\n' ' ')

  # Validate required env vars
  MISSING=""
  for VAR in $ENV_VARS; do
    if [[ -z "${!VAR}" ]]; then
      MISSING+="$VAR "
    fi
  done
  if [[ -n "$MISSING" ]]; then
    log_error "Missing required env vars for $APP_NAME: $MISSING"
    php -r "require 'app/classes/DeployAudit.php'; App\\Classes\\DeployAudit::record('$DEPLOYER','$GIT_SHA','$APP_NAME','failed');"
    REPORT=$(echo "$REPORT" | jq ".apps += [{\"name\": \"$APP_NAME\", \"status\": \"failed\"}]")
    if [[ "$FORCE" != true ]]; then
      log_error "Aborting deployment because of missing env vars. Use --force to continue."
      echo "$REPORT" > "$REPORT_FILE"
      exit 1
    fi
    ((INDEX++))
    continue
  fi

  # Execute steps (dry‑run skips actual execution)
  for STEP in build migrate healthcheck; do
    CMD_VAR=$(printf "%s_CMD" "$STEP")
    CMD=$(eval echo \$$CMD_VAR)
    log_info "[$APP_NAME] $STEP"
    if [[ "$DRY_RUN" = true ]]; then
      log_info "(dry‑run) $CMD"
      continue
    fi
    eval $CMD
    if [[ $? -ne 0 ]]; then
      log_error "[$APP_NAME] $STEP failed"
      php -r "require 'app/classes/DeployAudit.php'; App\\Classes\\DeployAudit::record('$DEPLOYER','$GIT_SHA','$APP_NAME','failed');"
      REPORT=$(echo "$REPORT" | jq ".apps += [{\"name\": \"$APP_NAME\", \"status\": \"failed\"}]")
      if [[ "$FORCE" != true ]]; then
        log_error "Aborting deployment. Use --force to continue."
        echo "$REPORT" > "$REPORT_FILE"
        exit 1
      else
        break
      fi
    fi
  done

  # If we reach here, the app succeeded
  php -r "require 'app/classes/DeployAudit.php'; App\\Classes\\DeployAudit::record('$DEPLOYER','$GIT_SHA','$APP_NAME','success');"
  REPORT=$(echo "$REPORT" | jq ".apps += [{\"name\": \"$APP_NAME\", \"status\": \"success\"}]")
  log_success "[$APP_NAME] deployment completed"

  ((INDEX++))
  unset STATUS
 done

# Write final JSON report
echo "$REPORT" > "$REPORT_FILE"
log_info "Deployment report written to $REPORT_FILE"

# ---------------------------------------------------------------------------
# Optional GitHub sync (only if not in CI mode)
# ---------------------------------------------------------------------------
if [[ "$CI_MODE" = false ]]; then
  read -p "Do you want to securely commit and push your safe files to GitHub? (y/n): " push_github </dev/tty
  if [[ "$push_github" == "y" ]]; then
    read -p "Enter a commit message: " commit_msg </dev/tty
    git add .
    git commit -m "$commit_msg"
    git push origin HEAD
    log_success "✅ Successfully synced to GitHub!"
  else
    log_info "⏭️ Skipping GitHub push."
  fi
fi

log_success "✅ Overall deployment complete"
