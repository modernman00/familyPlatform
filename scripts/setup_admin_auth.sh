#!/usr/bin/env bash
# ==============================================================================
# ⚡ UNIVERSAL DYNAMIC ADMIN AUTH & SCAFFOLDING ORCHESTRATOR v3.0
# ==============================================================================
# Multi-App Governance Charter (AGENTS.md)
# Target Portfolio: FamilyPlatform | LoanEasyFinance | PartyPlatform |
#                   TenantScore | ExecMind | iDecide | iAccount
# ==============================================================================

set -euo pipefail

export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

# Default configuration options
TARGET_DIR=""
CUSTOM_APP_NAME=""
CUSTOM_SLUG=""
FORCE_OVERWRITE=false
DRY_RUN=false

# ------------------------------------------------------------------------------
# CLI ARGUMENT PARSER
# ------------------------------------------------------------------------------
print_usage() {
    cat << EOF
Usage: $(basename "$0") [OPTIONS] [TARGET_DIR]

Options:
  --target-dir=PATH      Path to the target app root (defaults to parent of script dir)
  --app-name=NAME        Custom application brand name
  --slug=SLUG            Custom admin login path slug (e.g., /secadmin)
  --force                Overwrite existing boilerplate files
  --dry-run              Simulate execution without modifying files
  -h, --help             Show this help message

Examples:
  # Run against current repository
  bash scripts/setup_admin_auth.sh

  # Run against another portfolio app
  bash scripts/setup_admin_auth.sh --target-dir=../FamilyPlatform --app-name="FamilyPlatform"

  # Force regenerate templates with a specific secret path
  bash scripts/setup_admin_auth.sh --force --slug="/vault_admin"
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --target-dir=*)
            TARGET_DIR="${1#*=}"
            shift
            ;;
        --app-name=*)
            CUSTOM_APP_NAME="${1#*=}"
            shift
            ;;
        --slug=*)
            CUSTOM_SLUG="${1#*=}"
            shift
            ;;
        --force)
            FORCE_OVERWRITE=true
            shift
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        -h|--help)
            print_usage
            exit 0
            ;;
        *)
            if [ -z "$TARGET_DIR" ] && [ ! -d "$1" ]; then
                echo "❌ Unknown argument or directory not found: $1"
                print_usage
                exit 1
            elif [ -z "$TARGET_DIR" ]; then
                TARGET_DIR="$1"
                shift
            else
                echo "❌ Unexpected argument: $1"
                print_usage
                exit 1
            fi
            ;;
    esac
done

# Resolve Target Directory
if [ -z "$TARGET_DIR" ]; then
    TARGET_DIR="$(cd "$(dirname "$0")/.." && pwd)"
else
    TARGET_DIR="$(cd "$TARGET_DIR" && pwd)"
fi

cd "$TARGET_DIR"

# ------------------------------------------------------------------------------
# DYNAMIC BRAND & METADATA DETECTION
# ------------------------------------------------------------------------------
DETECTED_APP_NAME=""
if [ -f "${TARGET_DIR}/composer.json" ]; then
    DETECTED_APP_NAME=$(php -r "
        \$json = json_decode(file_get_contents('${TARGET_DIR}/composer.json'), true);
        \$name = \$json['name'] ?? '';
        echo \$name ? ucwords(str_replace(['/', '-', '_'], ' ', explode('/', \$name)[1] ?? \$name)) : '';
    " 2>/dev/null || true)
fi

if [ -z "$DETECTED_APP_NAME" ] && [ -f "${TARGET_DIR}/.env" ]; then
    DETECTED_APP_NAME=$(grep "^APP_NAME=" "${TARGET_DIR}/.env" | cut -d'=' -f2- | tr -d '"' | tr -d "'" || true)
fi

APP_NAME="${CUSTOM_APP_NAME:-${DETECTED_APP_NAME:-$(basename "$TARGET_DIR")}}"

# Detect Layout Blade Extension
DETECTED_LAYOUT="layouts.base"
if [ -f "${TARGET_DIR}/resources/views/layouts/base.blade.php" ]; then
    DETECTED_LAYOUT="layouts.base"
elif [ -f "${TARGET_DIR}/resources/views/layouts/app.blade.php" ]; then
    DETECTED_LAYOUT="layouts.app"
elif [ -f "${TARGET_DIR}/resources/views/layouts/main.blade.php" ]; then
    DETECTED_LAYOUT="layouts.main"
fi

echo "======================================================================"
echo " ⚡ UNIVERSAL DYNAMIC ADMIN SCAFFOLDING ORCHESTRATOR"
echo " Target Directory : ${TARGET_DIR}"
echo " Application Name : ${APP_NAME}"
echo " Detected Layout  : ${DETECTED_LAYOUT}"
echo " Force Overwrite  : ${FORCE_OVERWRITE}"
echo " Dry Run Mode     : ${DRY_RUN}"
echo " Timestamp        : $(date -u +%Y-%m-%dT%H:%M:%SZ)"
echo "======================================================================"

_write_file() {
    local filepath="$1"
    local content="$2"
    local description="$3"

    if [ -f "$filepath" ] && [ "$FORCE_OVERWRITE" = false ]; then
        echo "   ⏭️  Skipping existing: ${filepath#$TARGET_DIR/}"
        return
    fi

    echo "   ➕ [${description}]: ${filepath#$TARGET_DIR/}"
    if [ "$DRY_RUN" = false ]; then
        mkdir -p "$(dirname "$filepath")"
        cat << 'EOF' > "$filepath"
EOF
        printf "%s" "$content" > "$filepath"
    fi
}

# ------------------------------------------------------------------------------
# STEP 1: ECOSYSTEM & DEPENDENCY AUDIT
# ------------------------------------------------------------------------------
echo -e "\n📦 [1/6] Auditing Shared Library Ecosystem..."

if [ -f "${TARGET_DIR}/composer.json" ]; then
    if ! grep -q "modernman00/shared-lib" "${TARGET_DIR}/composer.json"; then
        echo "   ⚠️  Warning: 'modernman00/shared-lib' not detected in composer.json."
    else
        echo "   ✅ Backend shared-lib dependency verified in composer.json."
    fi
fi

if [ -f "${TARGET_DIR}/package.json" ]; then
    if ! grep -q "@modernman00/shared-js-lib" "${TARGET_DIR}/package.json"; then
        echo "   ⚠️  Warning: '@modernman00/shared-js-lib' not detected in package.json."
    else
        echo "   ✅ Frontend shared-js-lib dependency verified in package.json."
    fi
fi

# ------------------------------------------------------------------------------
# STEP 2: DYNAMIC ZERO-TRUST ENVIRONMENT SECRETS
# ------------------------------------------------------------------------------
echo -e "\n🔒 [2/6] Configuring Zero-Trust Parameters in .env..."
ENV_FILE="${TARGET_DIR}/.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "   ⚠️  No .env file found. Initializing .env..."
    if [ "$DRY_RUN" = false ]; then touch "$ENV_FILE"; fi
fi

_ensure_env_key() {
    local key="$1"
    local default_val="$2"
    if ! grep -q "^${key}=" "$ENV_FILE" 2>/dev/null; then
        echo "   ➕ Injecting missing security key: ${key}"
        if [ "$DRY_RUN" = false ]; then
            echo "${key}=${default_val}" >> "$ENV_FILE"
        fi
    fi
}

RANDOM_CODE=$(php -r "echo bin2hex(random_bytes(16));" 2>/dev/null || echo "sec_$(date +%s)_admin")
SLUG_GEN="${CUSTOM_SLUG:-/portal_$(php -r "echo bin2hex(random_bytes(6));" 2>/dev/null || echo "admin")}"

_ensure_env_key "ADMIN_SECRET_PATH" "${SLUG_GEN}"
_ensure_env_key "CODING" "${RANDOM_CODE}"
_ensure_env_key "ADMIN_ALLOWED_IPS" ""
_ensure_env_key "ADMIN_MAX_LOGIN_ATTEMPTS" "5"
_ensure_env_key "ADMIN_LOCKOUT_SECONDS" "900"
_ensure_env_key "ADMIN_SESSION_ROLE" "admin"

echo "   ✅ Environment secrets verified."

# ------------------------------------------------------------------------------
# STEP 3: CONTROLLER SCAFFOLDING
# ------------------------------------------------------------------------------
echo -e "\n⚙️  [3/6] Scaffolding Thin Admin Controllers..."

BASE_CONTROLLER_CONTENT=$(cat << 'EOF'
<?php

declare(strict_types=1);

namespace App\controller\admin;

use Src\Exceptions\UnauthorisedException;
use Src\functionality\SignIn;

class AdminBaseController
{
    /**
     * Centralized administrative session and role verification gate.
     *
     * @return array<string, mixed>
     * @throws UnauthorisedException
     */
    public static function verifyAdmin(): array
    {
        $roleKey = (string) ($_ENV['ADMIN_SESSION_ROLE'] ?? getenv('ADMIN_SESSION_ROLE') ?: 'admin');
        $data = SignIn::verify($roleKey);

        if (($data['role'] ?? '') !== $roleKey) {
            throw new UnauthorisedException('You are not authorized to access this administrative area.');
        }

        return $data;
    }
}
EOF
)

_write_file "${TARGET_DIR}/app/controller/admin/AdminBaseController.php" "$BASE_CONTROLLER_CONTENT" "Admin Base Security Controller"

DASHBOARD_CONTROLLER_CONTENT=$(cat << EOF
<?php

declare(strict_types=1);

namespace App\controller\admin;

use Src\Exceptions\UnauthorisedException;

class Dashboard extends AdminBaseController
{
    public function index(): void
    {
        try {
            \$admin = self::verifyAdmin();
            \$metrics = [
                'admin' => \$admin,
                'appName' => '${APP_NAME}',
                'title' => '${APP_NAME} | Admin Dashboard'
            ];

            view('admin/dashboard', \$metrics);
        } catch (\\Throwable \$e) {
            showError(\$e);
        }
    }
}
EOF
)

_write_file "${TARGET_DIR}/app/controller/admin/Dashboard.php" "$DASHBOARD_CONTROLLER_CONTENT" "Admin Dashboard Controller"

# ------------------------------------------------------------------------------
# STEP 4: ROUTER INTEGRATION
# ------------------------------------------------------------------------------
echo -e "\n🛣️  [4/6] Verifying Admin Router Definitions..."

ADMIN_ROUTER_CONTENT=$(cat << 'EOF'
<?php

declare(strict_types=1);

/**
 * @var AltoRouter $router
 */

// Admin Dashboard Route
$router->map('GET', '/admin/dashboard', 'App\controller\admin\Dashboard@index', 'admin_dashboard');
EOF
)

_write_file "${TARGET_DIR}/app/router/admin.php" "$ADMIN_ROUTER_CONTENT" "Admin Router Mapping"

# ------------------------------------------------------------------------------
# STEP 5: VIEWS & JAVASCRIPT ASSETS
# ------------------------------------------------------------------------------
echo -e "\n🎨 [5/6] Scaffolding Dynamic Blade Views & JavaScript Assets..."

LOGIN_VIEW_CONTENT=$(cat << EOF
@extends ('${DETECTED_LAYOUT}')
@section('title', '${APP_NAME} | Admin Sign In')
@section('data-page-id', 'loginAdminpage')

@section('head')
<style>
    .login-wrapper {
        animation: fadeScale 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: scale(0.98);
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
    .brand-gradient {
        background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
    }
    .input-field {
        transition: all 0.3s ease;
        background: #f8fafc;
        border: 1.5px solid transparent;
    }
    .input-field:focus {
        background: #ffffff;
        border-color: #3B82F6;
        box-shadow: 0 4px 14px -2px rgba(59, 130, 246, 0.15);
        outline: none;
    }
    .btn-submit {
        background: linear-gradient(135deg, #10B981, #059669);
        transition: all 0.3s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.4);
    }
    @keyframes fadeScale {
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
@endsection

@section('content')
<div class="login-wrapper w-full max-w-5xl mx-auto flex rounded-3xl overflow-hidden glass-panel border border-white/60 relative my-12">
    
    <!-- Left Brand Column -->
    <div class="hidden md:flex md:w-5/12 brand-gradient p-10 flex-col justify-between text-white relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl font-display font-bold leading-tight mb-4">
                ${APP_NAME} Admin Portal
            </h2>
            <p class="text-blue-100 font-light leading-relaxed">
                Protected administrative control center and analytics dashboard.
            </p>
        </div>
        <div class="relative z-10 flex items-center gap-3">
            <i class="fas fa-shield-alt text-2xl text-blue-200"></i>
            <div class="text-sm text-blue-100 font-medium">Bank-level Security<br><span class="text-xs text-blue-200/70 font-normal">256-bit encryption</span></div>
        </div>
    </div>

    <!-- Right Login Form Column -->
    <div class="w-full md:w-7/12 p-8 md:p-14 bg-white/50 relative">
        <div id="login-alert-container"></div>

        <div class="mb-8">
            <h1 class="text-3xl font-display font-extrabold text-gray-900 tracking-tight">Admin Sign In</h1>
            <p class="text-gray-500 mt-2 text-sm font-medium">Please enter your administrative credentials.</p>
        </div>

        <form id="loginadmin" class="myLoginForm space-y-5">
            @include('partials.loader', ['notificationId' => 'loginadmin'])

            <div>
                <label for="code" class="block text-sm font-semibold text-gray-700 mb-1.5">Security Passkey</label>
                <input type="password" id="code" name="code" class="input-field w-full px-4 py-3 rounded-xl text-gray-800 text-sm font-medium" placeholder="••••••••••••" required autocomplete="off">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" class="input-field w-full px-4 py-3 rounded-xl text-gray-800 text-sm font-medium" placeholder="admin@domain.com" required autocomplete="username">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                <input type="password" id="password" name="password" class="input-field w-full px-4 py-3 rounded-xl text-gray-800 text-sm font-medium" placeholder="••••••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit w-full py-3.5 px-4 rounded-xl text-white font-bold text-sm tracking-wide shadow-lg cursor-pointer">
                Authenticate & Enter
            </button>
        </form>
    </div>
</div>
@endsection
EOF
)

_write_file "${TARGET_DIR}/resources/views/auth/loginAdmin.blade.php" "$LOGIN_VIEW_CONTENT" "Admin Login Blade View"

LOGIN_JS_CONTENT=$(cat << 'EOF'
import { createAdminLoginHandler } from '@modernman00/shared-js-lib';

sessionStorage.removeItem('fromForgot');
sessionStorage.setItem("from", "adminLogin");

createAdminLoginHandler({
  formId: "loginadmin",
  route: window.location.pathname || "/loginadmin",
  redirect: "/admin/dashboard",
  theme: "tailwind",
  isCaptchaV3: true,
  recaptchaAction: "login",
});
EOF
)

_write_file "${TARGET_DIR}/resources/asset/js/auth/loginadmin.js" "$LOGIN_JS_CONTENT" "Admin Login JS Handler"

# ------------------------------------------------------------------------------
# STEP 6: SYNTAX & COMPLIANCE VALIDATION
# ------------------------------------------------------------------------------
echo -e "\n🔍 [6/6] Validating PHP Syntax..."
if [ "$DRY_RUN" = false ]; then
    find "${TARGET_DIR}/app/controller/admin" -name "*.php" -exec php -l {} \; > /dev/null
    echo "   ✅ PHP syntax check passed clean."
fi

echo -e "\n======================================================================"
echo " 🏆 DYNAMIC ADMIN AUTH SCAFFOLDING COMPLETE FOR: ${APP_NAME}"
echo "======================================================================"
SECRET_PATH=$(grep "^ADMIN_SECRET_PATH=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2- || echo "/loginadmin")
ADMIN_CODE=$(grep "^CODING=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2- || echo "[Not Set]")

echo " 📍 Admin Gateway URL   : ${SECRET_PATH}"
echo " 🔑 Security Passkey    : ${ADMIN_CODE}"
echo " 🛡️  Standard Compliance : Standard #1 (Immutable Lib) & Standard #2 (Shared-Lib Enforced)"
echo " 🚀 Reusability Command : bash scripts/setup_admin_auth.sh --target-dir=<path_to_app>"
echo "======================================================================"
