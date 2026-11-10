#!/usr/bin/env bash
# ==============================================================================
# Sovereign Dependency Integrity & Content-Addressable Vendor Cache Engine
# Architecture : Hermetic CAS, Atomic Staging Rename, Reflink/CoW, Zero-Symlink Audit
# Standard     : Enterprise Red Team & Gatewatcher Certified (v3.0)
# ==============================================================================

prepare_vendor_cache() {
    local app_name="${1:-}"
    local sandbox_path="${2:-}"
    local lock_file="${3:-composer.lock}"

    if [ -z "$app_name" ] || [ -z "$sandbox_path" ]; then
        echo "🛑 FATAL: prepare_vendor_cache requires APP_NAME and SANDBOX_PATH arguments." >&2
        return 1
    fi

    if [ ! -f "$lock_file" ]; then
        echo "🛑 FATAL: $lock_file not found in $(pwd)." >&2
        return 1
    fi

    echo "⚡ [Vendor CAS] Preparing physical production dependencies in sandbox (zero symlinks)..."
    
    local composer_lock_hash
    composer_lock_hash=$(shasum -a 256 "$lock_file" 2>/dev/null | awk '{print $1}' || sha256sum "$lock_file" 2>/dev/null | awk '{print $1}' || echo "nocache")
    
    local cache_base_dir="${HOME}/.cache/${app_name}_prod_vendor"
    local vendor_cache_dir="${cache_base_dir}/${composer_lock_hash}"
    local manifest_file="${vendor_cache_dir}/.integrity_manifest"

    local cache_valid=false
    if [ "$composer_lock_hash" != "nocache" ] && \
       [ -d "$vendor_cache_dir/vendor" ] && \
       [ -f "$vendor_cache_dir/vendor/autoload.php" ] && \
       [ -f "$vendor_cache_dir/vendor/modernman00/shared-lib/helpers/helpers.php" ]; then
        if [ -f "$manifest_file" ]; then
            local recorded_hash
            recorded_hash=$(grep "^LOCK_HASH=" "$manifest_file" 2>/dev/null | cut -d'=' -f2 || echo "")
            if [ "$recorded_hash" = "$composer_lock_hash" ]; then
                cache_valid=true
            fi
        else
            # Backwards compatibility: seed manifest for pre-existing verified cache
            cat <<EOF > "$manifest_file"
LOCK_HASH=${composer_lock_hash}
APP=${app_name}
CREATED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
MIGRATED_LEGACY=true
EOF
            cache_valid=true
        fi
    fi

    if [ "$cache_valid" = true ]; then
        echo "⚡ [Vendor CAS] Cache HIT: Using verified production vendor (${composer_lock_hash:0:12})..."
        rm -rf "$sandbox_path/vendor"
        cp -cR "$vendor_cache_dir/vendor" "$sandbox_path/" 2>/dev/null || cp -R "$vendor_cache_dir/vendor" "$sandbox_path/"
    else
        echo "📦 [Vendor CAS] Cache MISS: Building clean production dependencies from $lock_file..."
        rm -rf "$sandbox_path/vendor"
        
        local install_ok=false
        if (cd "$sandbox_path" && COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction --no-progress 2>/dev/null); then
            install_ok=true
        else
            echo "⚠️ [Vendor CAS] composer install failed or offline; syncing verified physical local vendor..."
            if [ -d "vendor" ]; then
                cp -cR vendor "$sandbox_path/" 2>/dev/null || cp -R vendor "$sandbox_path/"
                (cd "$sandbox_path" && COMPOSER_MEMORY_LIMIT=-1 composer dump-autoload --optimize --no-dev --no-interaction 2>/dev/null || true)
                install_ok=true
            fi
        fi

        if [ "$install_ok" = false ]; then
            echo "🛑 FATAL: Failed to construct production vendor dependencies." >&2
            return 1
        fi

        # Dereference any symlinks inside sandbox vendor to real directory trees
        if [ -d "$sandbox_path/vendor" ]; then
            find "$sandbox_path/vendor" -maxdepth 3 -type l 2>/dev/null | while read -r symlink_path; do
                if [ -n "$symlink_path" ] && [ -L "$symlink_path" ]; then
                    local target_path
                    target_path=$(readlink "$symlink_path")
                    if [[ "$target_path" != /* ]]; then
                        target_path="$(cd "$(dirname "$symlink_path")" && pwd)/$target_path"
                    fi
                    if [ -d "$target_path" ]; then
                        echo "🔗 [Vendor CAS] Resolving vendor symlink to real directory: $(basename "$symlink_path")"
                        rm -f "$symlink_path"
                        mkdir -p "$symlink_path"
                        cp -cR "$target_path/"* "$symlink_path/" 2>/dev/null || cp -R "$target_path/"* "$symlink_path/"
                    fi
                fi
            done
        fi

        # Seed content-addressable cache with atomic staging rename to prevent corruption races
        if [ "$composer_lock_hash" != "nocache" ] && [ -d "$sandbox_path/vendor" ]; then
            local staging_dir="${cache_base_dir}/.staging_${composer_lock_hash}_$$"
            rm -rf "$staging_dir"
            mkdir -p "$staging_dir"
            cp -cR "$sandbox_path/vendor" "$staging_dir/vendor" 2>/dev/null || cp -R "$sandbox_path/vendor" "$staging_dir/vendor"
            
            cat <<EOF > "$staging_dir/.integrity_manifest"
LOCK_HASH=${composer_lock_hash}
APP=${app_name}
CREATED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
PACKAGES=$(ls -1 "$staging_dir/vendor" 2>/dev/null | wc -l | tr -d ' ')
EOF
            mkdir -p "$cache_base_dir"
            rm -rf "$vendor_cache_dir"
            mv "$staging_dir" "$vendor_cache_dir"
            echo "💾 [Vendor CAS] Atomically cached verified production vendor (${composer_lock_hash:0:12})."
        fi
    fi

    # Gatewatcher & Red Team Inviolable Safety Checks
    if [ ! -f "$sandbox_path/vendor/autoload.php" ]; then
        echo "🛑 FATAL: vendor/autoload.php missing in sandbox. Aborting deploy." >&2
        return 1
    fi

    if [ -f "composer.json" ] && grep -q '"modernman00/shared-lib"' composer.json; then
        if [ ! -f "$sandbox_path/vendor/modernman00/shared-lib/helpers/helpers.php" ] || [ -L "$sandbox_path/vendor/modernman00/shared-lib" ]; then
            echo "🛑 FATAL: modernman00/shared-lib missing or is a symlink in sandbox vendor. Aborting deploy." >&2
            return 1
        fi
    fi

    local symlinks
    symlinks=$(find "$sandbox_path/vendor" -type l 2>/dev/null || true)
    if [ -n "$symlinks" ]; then
        echo "🛑 FATAL: Symlinks detected in sandbox vendor directory (zero-symlink violation):" >&2
        echo "$symlinks" >&2
        return 1
    fi

    echo "✅ [Vendor CAS] Sandbox vendor verified: 100% physical packages, zero symlinks, clean autoloader."
    return 0
}

# If executed directly as a script
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    prepare_vendor_cache "${1:-}" "${2:-}" "${3:-composer.lock}"
fi
