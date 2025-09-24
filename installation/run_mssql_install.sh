#!/usr/bin/env bash
set -Eeuo pipefail

# Runs create_db.sql, create_super_admin.sql, and comprehensive_admin_fix.sql
# in /var/www/ProjectTracker/sql, in that exact order.
# Prompts for SQL Server connection info and allows untrusted SSL (-C).
# Permanently adds /opt/mssql-tools18/bin to PATH.

SQL_DIR="/var/www/ProjectTracker/sql"
CREATE_SQL="${SQL_DIR}/create_db.sql"
SUPER_ADMIN_SQL="${SQL_DIR}/create_super_admin.sql"
ADMIN_FIX_SQL="${SQL_DIR}/comprehensive_admin_fix.sql"

log(){ echo "[mssql] $*"; }
err(){ echo "[mssql][ERROR] $*" >&2; }

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || { err "Required command not found: $1"; exit 1; }
}

check_files() {
  [[ -f "$CREATE_SQL"      ]] || { err "Missing: $CREATE_SQL"; exit 1; }
  [[ -f "$SUPER_ADMIN_SQL" ]] || { err "Missing: $SUPER_ADMIN_SQL"; exit 1; }
  [[ -f "$ADMIN_FIX_SQL"   ]] || { err "Missing: $ADMIN_FIX_SQL"; exit 1; }
}

ensure_path() {
  if ! grep -q '/opt/mssql-tools18/bin' "$HOME/.bashrc" 2>/dev/null; then
    log "Adding /opt/mssql-tools18/bin to PATH in ~/.bashrc"
    echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> "$HOME/.bashrc"
  fi
  export PATH="$PATH:/opt/mssql-tools18/bin"
}

prompt_conn() {
  read -r -p "SQL Server host (IP[,port]) [127.0.0.1,1433]: " MSSQL_HOST
  MSSQL_HOST="${MSSQL_HOST:-127.0.0.1,1433}"

  read -r -p "SQL username [sa]: " MSSQL_USER
  MSSQL_USER="${MSSQL_USER:-sa}"

  read -rs -p "SQL password: " MSSQL_PASS
  echo
}

run_sql() {
  local file="$1"
  log "--------------------------------------------"
  log "Running: $(basename "$file")"
  log "--------------------------------------------"
  sqlcmd -S "$MSSQL_HOST" -U "$MSSQL_USER" -P "$MSSQL_PASS" -b -l 30 -C -i "$file"
  log "Finished: $(basename "$file")"
}

main() {
  ensure_path
  require_cmd sqlcmd
  check_files
  prompt_conn

  run_sql "$CREATE_SQL"
  run_sql "$SUPER_ADMIN_SQL"
  run_sql "$ADMIN_FIX_SQL"

  log "All scripts executed successfully."
  log "NOTE: /opt/mssql-tools18/bin has been added to PATH in ~/.bashrc for future sessions."
}

main "$@"
