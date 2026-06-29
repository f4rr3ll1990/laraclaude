#!/usr/bin/env bash
#
# F4X — запуск сайта для публичного доступа (через Cloudflare → роутер → :8000).
#
# Делает:
#   1. Проверяет MySQL (LAMPP), при необходимости запускает его (нужен sudo).
#   2. Гарантирует прод-сборку ассетов и убирает dev-режим Vite (public/hot).
#   3. Поднимает `php artisan serve` на 0.0.0.0:8000 (доступен из LAN/роутера).
#
# Запуск:  ./start.sh
#
set -euo pipefail

# ── настройки ────────────────────────────────────────────────────────────
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HOST="0.0.0.0"
PORT="8000"
DB_HOST="127.0.0.1"
DB_PORT="3306"
LAMPP="/opt/lampp/lampp"
LOG="$APP_DIR/storage/logs/serve.log"
PIDFILE="$APP_DIR/storage/serve.pid"
QUEUE_LOG="$APP_DIR/storage/logs/queue.log"
QUEUE_PIDFILE="$APP_DIR/storage/queue.pid"

# ── вывод ────────────────────────────────────────────────────────────────
c_ok()   { printf '\033[32m✔\033[0m %s\n' "$*"; }
c_info() { printf '\033[36m›\033[0m %s\n' "$*"; }
c_warn() { printf '\033[33m!\033[0m %s\n' "$*"; }
c_err()  { printf '\033[31m✘\033[0m %s\n' "$*" >&2; }

# Проверка TCP-порта без внешних утилит (bash /dev/tcp).
port_open() { timeout 2 bash -c ">/dev/tcp/$1/$2" 2>/dev/null; }

cd "$APP_DIR"

# ── 1. MySQL ─────────────────────────────────────────────────────────────
c_info "Проверяю MySQL на $DB_HOST:$DB_PORT…"
if port_open "$DB_HOST" "$DB_PORT"; then
    c_ok "MySQL уже работает."
else
    c_warn "MySQL не отвечает — запускаю LAMPP (нужен sudo)…"
    if [[ ! -x "$LAMPP" ]]; then
        c_err "Не найден $LAMPP. Запусти MySQL вручную и повтори."
        exit 1
    fi
    sudo "$LAMPP" startmysql
    # ждём появления порта до 30 секунд
    for _ in $(seq 1 30); do
        port_open "$DB_HOST" "$DB_PORT" && break
        sleep 1
    done
    if port_open "$DB_HOST" "$DB_PORT"; then
        c_ok "MySQL запущен."
    else
        c_err "MySQL так и не поднялся на $DB_HOST:$DB_PORT."
        exit 1
    fi
fi

# ── 2. Прод-ассеты ───────────────────────────────────────────────────────
# public/hot заставляет @vite отдавать ссылки на dev-сервер (5173) — для
# публичного доступа он не нужен.
if [[ -f public/hot ]]; then
    c_warn "Убираю public/hot (Vite dev-режим)."
    rm -f public/hot
fi

if [[ ! -f public/build/manifest.json ]]; then
    c_warn "Сборки нет — собираю ассеты (npm run build)…"
    npm run build
    c_ok "Ассеты собраны."
else
    c_ok "Прод-сборка на месте (public/build)."
fi

# Симлинк public/storage нужен, чтобы сгенерированные Gemini обложки
# (storage/app/public/articles/*) были доступны по URL /storage/...
if [[ ! -e public/storage ]]; then
    c_warn "Нет симлинка public/storage — создаю (php artisan storage:link)…"
    php artisan storage:link
    c_ok "Симлинк создан."
else
    c_ok "Симлинк public/storage на месте."
fi

# ── 3. artisan serve ─────────────────────────────────────────────────────
# Уже запущен?
if [[ -f "$PIDFILE" ]] && kill -0 "$(cat "$PIDFILE")" 2>/dev/null; then
    c_warn "Сервер уже запущен (PID $(cat "$PIDFILE")). Перезапускаю."
    "$APP_DIR/stop.sh" || true
fi
if port_open "127.0.0.1" "$PORT"; then
    c_warn "Порт $PORT уже занят — пробую освободить."
    pkill -f "artisan serve" 2>/dev/null || true
    sleep 1
fi

c_info "Запускаю artisan serve на $HOST:$PORT…"
setsid nohup php artisan serve --host="$HOST" --port="$PORT" \
    > "$LOG" 2>&1 < /dev/null &
echo $! > "$PIDFILE"
disown || true

# проверка отклика
for _ in $(seq 1 10); do
    port_open "127.0.0.1" "$PORT" && break
    sleep 1
done

if port_open "127.0.0.1" "$PORT"; then
    c_ok "Сервер поднят (PID $(cat "$PIDFILE")). Лог: $LOG"
    echo
    c_ok "Локально:  http://127.0.0.1:$PORT"
    c_ok "Публично:  https://f4x.pp.ua"
else
    c_err "Сервер не ответил. Лог:"
    tail -20 "$LOG" >&2
    exit 1
fi

# ── 4. Очередь (генерация обложек через Gemini) ──────────────────────────
# POST /api/news ставит задачу GenerateArticleImage в очередь database —
# без воркера обложки никогда не сгенерируются.
if [[ -f "$QUEUE_PIDFILE" ]] && kill -0 "$(cat "$QUEUE_PIDFILE")" 2>/dev/null; then
    c_warn "Воркер очереди уже запущен (PID $(cat "$QUEUE_PIDFILE")). Перезапускаю."
    kill "$(cat "$QUEUE_PIDFILE")" 2>/dev/null || true
    sleep 1
fi

c_info "Запускаю queue:work…"
setsid nohup php artisan queue:work --tries=3 --sleep=3 \
    > "$QUEUE_LOG" 2>&1 < /dev/null &
echo $! > "$QUEUE_PIDFILE"
disown || true
c_ok "Воркер очереди поднят (PID $(cat "$QUEUE_PIDFILE")). Лог: $QUEUE_LOG"
