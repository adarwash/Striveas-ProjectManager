#!/usr/bin/env bash
set -Eeuo pipefail

# Web stack installer for Ubuntu + conditional Git deploy
# - curl, git, Apache, PHP (+common)
# - PDO MySQL + SQL Server (ODBC18 + PECL drivers)
# - Apache ssl + rewrite
# - Self-signed certs
# - Removes /var/www/html
# - If /var/www/ProjectTracker DOES NOT exist: clones repo there
# - SSL vhost in /etc/apache2/sites-available/000-default.conf (port 443)
# - Docroot = /var/www/ProjectTracker/public

SERVER_NAME=""
PROJECT_DIR="/var/www/ProjectTracker"
DOCROOT="/var/www/ProjectTracker/public"
REPO_URL="https://github.com/adarwash/Striveas-ProjectManager.git"
REPO_BRANCH="main"

log(){ echo "[install] $*"; }
err(){ echo "[install][ERROR] $*" >&2; }

usage() {
  cat <<EOF
Usage: sudo bash $0 [--server-name example.com] [--project-dir /var/www/ProjectTracker] [--docroot /var/www/ProjectTracker/public] [--repo-url <url>] [--repo-branch <branch>]
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --server-name) SERVER_NAME="$2"; shift 2;;
    --project-dir) PROJECT_DIR="$2"; DOCROOT="$2/public"; shift 2;;
    --docroot) DOCROOT="$2"; shift 2;;
    --repo-url) REPO_URL="$2"; shift 2;;
    --repo-branch) REPO_BRANCH="$2"; shift 2;;
    -h|--help) usage; exit 0;;
    *) err "Unknown arg: $1"; usage; exit 1;;
  esac
done

require_root() {
  if [[ $EUID -ne 0 ]]; then
    err "Please run as root: sudo bash $0"
    exit 1
  fi
}

prompt_inputs() {
  if [[ -z "$SERVER_NAME" ]]; then
    read -r -p "Enter domain (ServerName) [hiveitportal.com]: " SERVER_NAME
    SERVER_NAME="${SERVER_NAME:-hiveitportal.com}"
  fi
}

install_stack() {
  log "Updating apt and installing core tools…"
  apt-get update -y
  apt-get install -y curl gpg lsb-release software-properties-common ca-certificates git

  log "Installing Apache…"
  apt-get install -y apache2
  a2enmod ssl >/dev/null || true
  a2enmod rewrite >/dev/null || true

  log "Installing PHP and common extensions…"
  apt-get install -y php php-cli php-common php-xml php-mbstring php-curl php-zip libapache2-mod-php

  a2dismod mpm_event >/dev/null 2>&1 || true
  a2enmod mpm_prefork >/dev/null 2>&1 || true

  log "Installing PDO MySQL…"
  apt-get install -y php-mysql

  log "Setting up Microsoft repo for SQL Server ODBC 18…"
  . /etc/os-release || true
  UBU_VER="${VERSION_ID:-22.04}"
  if [[ ! -f /usr/share/keyrings/microsoft-prod.gpg ]]; then
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg
  fi
  echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/ubuntu/${UBU_VER}/prod $(lsb_release -cs) main" \
    > /etc/apt/sources.list.d/mssql-release.list || true
  apt-get update -y || true
  ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 || true
  if ! grep -q "/opt/mssql-tools18/bin" /etc/profile.d/mssql-tools.sh 2>/dev/null; then
    echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' > /etc/profile.d/mssql-tools.sh
  fi

  log "Installing PECL drivers (sqlsrv, pdo_sqlsrv)…"
  apt-get install -y php-pear php-dev build-essential unixodbc-dev
  pecl channel-update pecl.php.net || true
  printf "\n" | pecl install -f sqlsrv || true
  printf "\n" | pecl install -f pdo_sqlsrv || true

  PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')"
  MODS_DIR="/etc/php/${PHP_VER}/mods-available"
  mkdir -p "$MODS_DIR"
  echo "extension=sqlsrv.so"     > "$MODS_DIR/sqlsrv.ini"
  echo "extension=pdo_sqlsrv.so" > "$MODS_DIR/pdo_sqlsrv.ini"
  if command -v phpenmod >/dev/null 2>&1; then
    phpenmod -v "$PHP_VER" -s ALL sqlsrv pdo_sqlsrv || phpenmod sqlsrv pdo_sqlsrv
  else
    PHP_INI="/etc/php/${PHP_VER}/apache2/php.ini"
    touch "$PHP_INI"
    grep -q '^extension=sqlsrv' "$PHP_INI" || echo 'extension=sqlsrv' >> "$PHP_INI"
    grep -q '^extension=pdo_sqlsrv' "$PHP_INI" || echo 'extension=pdo_sqlsrv' >> "$PHP_INI"
  fi
}

prepare_web_root() {
  log "Removing default Apache html folder if it exists…"
  [[ -d /var/www/html ]] && rm -rf /var/www/html && log "Removed /var/www/html"
}

conditional_clone_repo() {
  if [[ -d "$PROJECT_DIR" ]]; then
    log "Project directory exists: ${PROJECT_DIR} — skipping git clone."
  else
    log "Project directory not found. Cloning ${REPO_URL} into ${PROJECT_DIR}…"
    mkdir -p "$(dirname "$PROJECT_DIR")"
    git clone --branch "${REPO_BRANCH}" --depth 1 "${REPO_URL}" "${PROJECT_DIR}"
    log "Clone complete."
  fi

  # Ensure docroot exists
  if [[ ! -d "${DOCROOT}" ]]; then
    log "Docroot ${DOCROOT} not found; creating it."
    mkdir -p "${DOCROOT}"
    [[ -f "${DOCROOT}/index.php" ]] || echo "<?php phpinfo(); ?>" > "${DOCROOT}/index.php"
  fi

  # Ownership to www-data
  id www-data >/dev/null 2>&1 && chown -R www-data:www-data "${PROJECT_DIR}"
}

create_self_signed_cert() {
  local CERT="/etc/ssl/certificate.crt"
  local KEY="/etc/ssl/private.key"
  local CHAIN="/etc/ssl/ca_bundle.crt"
  if [[ ! -f "$KEY" || ! -f "$CERT" ]]; then
    log "Generating self-signed cert for ${SERVER_NAME}"
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
      -keyout "$KEY" -out "$CERT" \
      -subj "/C=GB/ST=England/L=Portsmouth/O=IT Support/CN=${SERVER_NAME}" \
      -addext "subjectAltName=DNS:${SERVER_NAME},DNS:localhost,IP:127.0.0.1"
    chmod 600 "$KEY"; chmod 644 "$CERT"
  else
    log "Existing cert/key found; skipping generation."
  fi
  [[ -f "$CHAIN" ]] || { cp -f "$CERT" "$CHAIN"; chmod 644 "$CHAIN"; }
}

write_vhost() {
  local VHOST="/etc/apache2/sites-available/000-default.conf"
  log "Backing up ${VHOST} to ${VHOST}.bak"
  cp -f "$VHOST" "${VHOST}.bak" 2>/dev/null || true

  # Ensure Apache listens on 443
  grep -qE '^\s*Listen\s+443' /etc/apache2/ports.conf 2>/dev/null || echo "Listen 443" >> /etc/apache2/ports.conf

  log "Writing SSL vhost to ${VHOST}"
  cat > "$VHOST" <<EOF
<VirtualHost *:443>
    DocumentRoot ${DOCROOT}
    ServerName ${SERVER_NAME}
    <Directory ${DOCROOT}>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    SSLEngine               on
    SSLCertificateFile      /etc/ssl/certificate.crt
    SSLCertificateKeyFile   /etc/ssl/private.key
    SSLCertificateChainFile /etc/ssl/ca_bundle.crt

    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>
EOF

  a2ensite 000-default.conf >/dev/null 2>&1 || true

  log "Validating Apache config…"
  if apache2ctl configtest; then
    systemctl enable apache2 >/dev/null 2>&1 || true
    systemctl restart apache2
    log "Apache reloaded successfully."
  else
    err "apache2ctl configtest failed. Restoring previous vhost."
    cp -f "${VHOST}.bak" "$VHOST" 2>/dev/null || true
    exit 1
  fi
}

verify_php_exts() {
  log "Verifying PHP extensions (CLI)…"
  php -m | grep -E '(^| )sqlsrv( |$)'     >/dev/null || err "sqlsrv NOT loaded in CLI"
  php -m | grep -E '(^| )pdo_sqlsrv( |$)' >/dev/null || err "pdo_sqlsrv NOT loaded in CLI"
}

main() {
  require_root
  prompt_inputs
  install_stack
  prepare_web_root
  conditional_clone_repo
  create_self_signed_cert
  write_vhost
  verify_php_exts

  log "Done!"
  log "ServerName: ${SERVER_NAME}"
  log "Project:    ${PROJECT_DIR}"
  log "Docroot:    ${DOCROOT}"
  log "Repo:       ${REPO_URL} (branch: ${REPO_BRANCH})"
  log "Visit:      https://${SERVER_NAME}  (self-signed cert; browser warning expected)"
}

main "$@"
