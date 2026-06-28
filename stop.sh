#!/usr/bin/env bash
#
# F4X — остановка сайта.
#
# По умолчанию глушит только artisan serve. MySQL (LAMPP) остаётся работать;
# чтобы остановить и его, запусти:  ./stop.sh --mysql
#
set -uo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PIDFILE="$APP_DIR/storage/serve.pid"
LAMPP="/opt/lampp/lampp"

c_ok()   { printf '\033[32m✔\033[0m %s\n' "$*"; }
c_info() { printf '\033[36m›\033[0m %s\n' "$*"; }
c_warn() { printf '\033[33m!\033[0m %s\n' "$*"; }

# ── artisan serve ────────────────────────────────────────────────────────
if [[ -f "$PIDFILE" ]] && kill -0 "$(cat "$PIDFILE")" 2>/dev/null; then
    PID="$(cat "$PIDFILE")"
    c_info "Останавливаю artisan serve (PID $PID)…"
    kill "$PID" 2>/dev/null || true
    sleep 1
    kill -9 "$PID" 2>/dev/null || true
    rm -f "$PIDFILE"
    c_ok "Сервер остановлен."
else
    # подстраховка: вдруг PID-файла нет, но процесс висит
    if pgrep -f "artisan serve" >/dev/null; then
        c_warn "PID-файла нет, глушу по имени процесса."
        pkill -f "artisan serve" 2>/dev/null || true
        c_ok "Сервер остановлен."
    else
        c_warn "artisan serve не запущен."
    fi
    rm -f "$PIDFILE"
fi

# ── MySQL (по флагу) ─────────────────────────────────────────────────────
if [[ "${1:-}" == "--mysql" ]]; then
    c_info "Останавливаю MySQL (LAMPP, нужен sudo)…"
    sudo "$LAMPP" stopmysql
    c_ok "MySQL остановлен."
fi
