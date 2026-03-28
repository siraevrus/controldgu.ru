# Control DGU — мониторинг ДГУ (демо / MVP)

Laravel 13, SQLite, Blade + Tailwind (Breeze), Spatie Permission. Телеметрия от симулятора по HTTP или командой Artisan.

## Быстрый старт

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

- Вход администратора: `admin@controldgu.local` / `password`
- Демо-токен приёма телеметрии: `demo-ingest-token` (сидер создаёт ДГУ `AD100C-DEMO-001`)
- Симуляция пакета: `php artisan dgu:simulate-telemetry`
- API: `POST /api/v1/dgus/{public_id}/telemetry` с заголовком `Authorization: Bearer demo-ingest-token` и телом `{"values":{...}}`

Подробный план этапов: [docs/DEVELOPMENT_PLAN.md](docs/DEVELOPMENT_PLAN.md).

Composer в PATH может отсутствовать; при необходимости: `php /path/to/composer.phar` или локальная установка с [getcomposer.org](https://getcomposer.org).

---

Проект основан на шаблоне Laravel; ниже — стандартная справка фреймворка.
