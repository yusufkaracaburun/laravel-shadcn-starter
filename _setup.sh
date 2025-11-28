#!/usr/bin/env bash
set -euo pipefail

#######################
# Configuratie — pas dit aan naar wens
#######################
API_DIR="api"
CLIENT_DIR="client"
DB_FILE="database/database.sqlite"

# MailHog settings
MAILHOG_SMTP_HOST="127.0.0.1"
MAILHOG_SMTP_PORT="1025"
MAILHOG_WEB_PORT="8025"

# Git-repo CLIENT
SHADCNVUE_ADMIN_REPO="git@github.com:Whbbit1999/shadcn-vue-admin.git"

# Cleanup function for background processes
cleanup() {
  [ ! -z "${API_PID-}" ] && kill "$API_PID" 2>/dev/null || true
  [ ! -z "${CLIENT_PID-}" ] && kill "$CLIENT_PID" 2>/dev/null || true
  [ ! -z "${MAILHOG_PID-}" ] && kill "$MAILHOG_PID" 2>/dev/null || true
}
trap cleanup EXIT ERR INT TERM

# Detect sed command voor macOS vs Linux
if [[ "$OSTYPE" == "darwin"* ]]; then
  SED_IN_PLACE="sed -i ''"
else
  SED_IN_PLACE="sed -i"
fi

echo "=== Start setup: Laravel 12 API + shadcn-vue-admin CLIENT + SQLite + MailHog ==="

# Vereiste checks
for cmd in composer php curl git npm; do
  if ! command -v "$cmd" > /dev/null 2>&1; then
    echo "❌ Vereist commando '$cmd' is niet geïnstalleerd. Installeer het eerst." >&2
    exit 1
  fi
done

# Check of directories al bestaan
if [ -d "$API_DIR" ]; then
  echo "⚠️  API directory '$API_DIR' bestaat al — API setup wordt overgeslagen."
  SKIP_API=true
fi
if [ -d "$CLIENT_DIR" ]; then
  echo "⚠️  CLIENT directory '$CLIENT_DIR' bestaat al — CLIENT setup wordt overgeslagen."
  SKIP_CLIENT=true
fi

#######################
# 1) Laravel API
#######################
if [ -z "${SKIP_API-}" ]; then
  echo "--- Creating Laravel API in '$API_DIR' ---"
  composer create-project laravel/laravel:^12 "$API_DIR"
  cd "$API_DIR"

  echo "--- Configuring .env (SQLite + MailHog) ---"
  cp .env.example .env
  mkdir -p "$(dirname "$DB_FILE")"
  touch "$DB_FILE"
  $SED_IN_PLACE "s/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/" .env
  $SED_IN_PLACE "s#^DB_DATABASE=.*#DB_DATABASE=${DB_FILE}#" .env

  php artisan key:generate

  cat >> .env <<EOL

MAIL_MAILER=smtp
MAIL_HOST=${MAILHOG_SMTP_HOST}
MAIL_PORT=${MAILHOG_SMTP_PORT}
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
EOL

  echo "--- Installing Fortify & Sanctum ---"
  composer require laravel/fortify laravel/sanctum
  php artisan vendor:publish --provider="Laravel\\Fortify\\FortifyServiceProvider" --tag=config
  php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag=config
  if [ ! -f config/fortify.php ]; then
    echo "⚠️  Fortify config not found after publish — copying stub"
    cp vendor/laravel/fortify/config/fortify.php config/fortify.php
  fi
  php artisan migrate --force

  echo "--- Configuring Fortify for API-mode (views=false) ---"
  php -r "file_exists('config/fortify.php') && file_put_contents('config/fortify.php', preg_replace(\"/'views'\\s*=>\\s*true/\",\"'views' => false\", file_get_contents('config/fortify.php')));"

  cd ..
else
  echo "--- Skipping API setup" 
fi

#######################
# 2) CLIENT: clone shadcn-vue-admin
#######################
if [ -z "${SKIP_CLIENT-}" ]; then
  echo "--- Cloning shadcn-vue-admin into '$CLIENT_DIR' ---"
  if ! git clone "$SHADCNVUE_ADMIN_REPO" "$CLIENT_DIR"; then
    echo "❌ git clone failed — check SSH-key or repository URL." >&2
    exit 1
  fi

  cd "$CLIENT_DIR"
  echo "--- Adjusting CLIENT dependencies for npm compatibility ---"
  node <<'NODE'
const fs = require('fs');
const pkgPath = 'package.json';
const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
const desired = {
  dependencies: {
    zod: '^3.24.2',
  },
  devDependencies: {
    '@tailwindcss/vite': '^4.0.7',
    '@vitejs/plugin-vue': '^5.1.4',
    '@vitejs/plugin-vue-jsx': '^3.1.0',
    vite: '^5.4.11',
    'vite-plugin-vue-devtools': '^7.7.9',
  },
};
let changed = false;
for (const [section, deps] of Object.entries(desired)) {
  pkg[section] = pkg[section] || {};
  for (const [name, version] of Object.entries(deps)) {
    if (pkg[section][name] !== version) {
      pkg[section][name] = version;
      changed = true;
    }
  }
}
if (changed) {
  fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 2) + '\n');
}
NODE

  if ! npm install; then
    echo "❌ npm install failed in CLIENT — controleer je Node/npm omgeving." >&2
    exit 1
  fi

  if [ ! -f .env ]; then
    cat > .env <<EOENV
VITE_SERVER_API_URL=http://127.0.0.1:8000
VITE_SERVER_API_PREFIX=/api
VITE_SERVER_API_TIMEOUT=5000
EOENV
    echo "✅ Created CLIENT .env with default API settings"
  else
    echo "↷ CLIENT .env already exists — leaving it untouched"
  fi

  echo "--- Fixing Zod validation in env.ts for compatibility ---"
  if [ -f src/utils/env.ts ]; then
    $SED_IN_PLACE "s/z\.url()/z.string().url()/g" src/utils/env.ts
    echo "✅ Fixed env.ts Zod validation"
  fi

  cd ..
else
  echo "--- Skipping CLIENT setup"
fi

#######################
# 3) (Optioneel) MailHog starten
#######################
MAILHOG_CMD=""
if command -v MailHog > /dev/null 2>&1; then
  MAILHOG_CMD="MailHog"
elif command -v mailhog > /dev/null 2>&1; then
  MAILHOG_CMD="mailhog"
fi

if [ ! -z "$MAILHOG_CMD" ]; then
  echo "--- Starting MailHog SMTP server ---"
  "$MAILHOG_CMD" > /dev/null 2>&1 &
  MAILHOG_PID=$!
  sleep 2
  echo "MailHog running: SMTP ${MAILHOG_SMTP_HOST}:${MAILHOG_SMTP_PORT}, Web UI: http://127.0.0.1:${MAILHOG_WEB_PORT}"
else
  echo "⚠️ MailHog niet gevonden — mails kunnen niet lokaal opgehaald worden"
fi

#######################
# 4) Test API + CLIENT servers
#######################
if [ -z "${SKIP_API-}" ]; then
  echo "--- Testing API server ---"
  cd "$API_DIR"
  php artisan serve --port=8000 > /dev/null 2>&1 &
  API_PID=$!
  API_OK=false
  for _ in {1..10}; do
    if curl -s http://127.0.0.1:8000 | grep -q "Laravel"; then
      API_OK=true
      break
    fi
    sleep 1
  done
  if [ "$API_OK" = true ]; then
    echo "✅ API is up"
  else
    echo "❌ API failed to start" >&2
    exit 1
  fi
  kill "$API_PID" 2>/dev/null || true
  cd ..
else
  echo "↷ Skipping API test (existing directory)"
fi

if [ -z "${SKIP_CLIENT-}" ]; then
  echo "--- Testing CLIENT (Vite) server ---"
  cd "$CLIENT_DIR"
  npm run dev > /dev/null 2>&1 &
  CLIENT_PID=$!
  CLIENT_OK=false
  for _ in {1..15}; do
    if curl -s http://127.0.0.1:5173 | grep -q "<!DOCTYPE html>"; then
      CLIENT_OK=true
      break
    fi
    sleep 1
  done
  if [ "$CLIENT_OK" = true ]; then
    echo "✅ CLIENT is up"
  else
    echo "❌ CLIENT failed to start" >&2
    exit 1
  fi
  kill "$CLIENT_PID" 2>/dev/null || true
  cd ..
else
  echo "↷ Skipping CLIENT test (existing directory)"
fi

echo "=== Setup voltooid ==="
echo "👉 API in ./$API_DIR"
echo "👉 CLIENT (shadcn-vue-admin) in ./$CLIENT_DIR"
if [ ! -z "${MAILHOG_PID-}" ]; then
  echo "📬 MailHog Web UI: http://127.0.0.1:${MAILHOG_WEB_PORT}"
fi

exit 0