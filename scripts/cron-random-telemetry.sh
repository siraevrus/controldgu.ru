#!/usr/bin/env bash
# Каждую минуту: случайная телеметрия по всем ДГУ.
# Crontab (подставьте путь к php и к проекту):
# * * * * * /var/www/controldgu.ru/scripts/cron-random-telemetry.sh >> /var/log/controldgu-telemetry-cron.log 2>&1

set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if command -v php >/dev/null 2>&1; then
  exec php artisan dgu:simulate-random-telemetry --all
fi

echo "php not found in PATH" >&2
exit 1
