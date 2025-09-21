# User Balances — Laravel 12 + Vue 3 (Pinia, Vite)

Проект демонтстрирует работу пользовательских балансов с очередями и фоновыми операциями. Фронтенд — SPA на Vue 3 (Pinia, Vue Router, Vite), бэкенд — Laravel 12 (Sanctum для токенов, очереди для обработки операций).

## Стек и ключевые особенности
- Backend: Laravel 12, Sanctum, очереди (database или Redis), миграции/сидеры
- Frontend: Vue 3 + Vite, Pinia, Vue Router, Bootstrap 5
- API: регистрация/логин, баланс, операции (создание/история), защита маршрутов
- UX: автообновление данных (polling), история с пагинацией/поиском, ожидание изменения баланса после операции

## Быстрый старт (TL;DR)
```bash
# 1) Установить зависимости
composer install
npm ci # или npm install

# 2) Настроить .env и ключ
cp .env.example .env
php artisan key:generate

# 3) Настроить БД и очередь, затем миграции
php artisan migrate

# 4) Запустить всё одной командой (PHP сервер, queue worker, логи, Vite)
composer run dev
# SPA доступна по http://127.0.0.1:8000
```

## Требования
- PHP >= 8.2
- Composer >= 2
- Node.js >= 18 (рекомендуется 20) и npm
- СУБД: SQLite/PostgreSQL/MySQL — на выбор (настроить в `.env`)
- Очереди: Redis (рекомендуется) или database driver

## Установка и запуск (Dev)
1) Клонировать репозиторий и перейти в каталог проекта.

2) Backend:
- Установить зависимости
  ```bash
  composer install
  ```
- Скопировать окружение и сгенерировать APP_KEY
  ```bash
  cp .env.example .env
  php artisan key:generate
  ```
- Настроить подключение к БД в `.env` (DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- Применить миграции (создаст таблицы пользователей, токенов, очереди, балансов и операций)
  ```bash
  php artisan migrate
  ```
- Очереди (2 варианта):
  - Redis (рекомендуется)
    - Установите и запустите Redis
    - В `.env` установите `QUEUE_CONNECTION=redis` и при необходимости `REDIS_*`
  - Database (без Redis)
    - Убедитесь, что `QUEUE_CONNECTION=database`
    - Если таблица jobs ещё не создана: `php artisan queue:table && php artisan migrate`

3) Frontend:
- Установить зависимости
  ```bash
  npm ci  # или npm install
  ```

4) Запуск (всё сразу)
- Удобный способ — один процесс с несколькими воркерами:
  ```bash
  composer run dev
  ```
  Это поднимет:
  - PHP сервер (http://127.0.0.1:8000)
  - Очередь: `php artisan queue:work`
  - Логи: `php artisan pail`
  - Vite dev-server (HMR) для фронтенда

5) Альтернативный ручной запуск (отдельными процессами)
```bash
# Терминал 1 — PHP
php artisan serve

# Терминал 2 — очередь
php artisan queue:work --sleep=0 --tries=1 --queue=default

# Терминал 3 — фронтенд
npm run dev
```

## Скрипты
Backend (Composer):
- `composer dev` — dev-окружение: сервер, очередь, логи, Vite (через concurrently)
- `composer test` — запустить тесты (`php artisan test`)
- `composer dev:all` — набор инструментов разработчика: ide-helper, rector, phpstan, pint

Frontend (npm):
- `npm run dev` — Vite dev server
- `npm run build` — Vite production build (в `public/build`)
- `npm run lint` — ESLint по `resources/js`
- `npm run lint:fix` — ESLint с автофиксами
- `npm run format` — Prettier для blade и vue-файлов

## Навигация (SPA)
Vue Router (`resources/js/router.js`):
- `/balance` — страница баланса + форма создания операции (защищена)
- `/dashboard` — карточка баланса и последние 5 операций (защищена)
- `/history` — полная история операций с поиском/пагинацией (защищена)
- `/login`, `/register` — гостевые страницы (редиректят на `/balance` при аутентификации)

Маршруты защищены через Pinia `userStore` и middleware перед роутингом. Токен хранится в `localStorage` (ключ `auth_token`).

## Архитектура фронтенда
`resources/js/`:
- `views/` — страницы: `Balance.vue`, `Dashboard.vue`, `History.vue`, `Login.vue`, `Register.vue`
- `components/` — переиспользуемые компоненты: `BalanceCard.vue`, `OperationsTable.vue`, `Header.vue`, `MainLayout.vue`
- `store/` (Pinia):
  - `userStore.js` — аутентификация (login/register/logout, `initializeAuth`), хранение `user` и `token`
  - `balanceStore.js` — состояние баланса, `fetchBalance()`, ожидание изменения (`waitForBalanceChange`)
  - `operationStore.js` — список операций, пагинация/поиск, `fetchOperations()`, `createOperation()`
- `utils/` — утилиты форматирования: `date.js` (`formatDate`), `money.js` (`formatAmount`)

Примечание: в текущей версии клиент обновляет данные polling’ом (5–10 с). В бэкенде существуют SSE-эндпоинты (`/sse/balance`, `/sse/operations`), их можно задействовать позже.

## API (сводка)
Базовый URL для фронтенда: `/api` (см. `resources/js/api/index.js`). Авторизация — Bearer-токен (`Authorization: Bearer <token>`), выдаётся на `POST /api/login` и сохраняется в `localStorage`.

Аутентификация:
- `POST /api/register` — регистрация, тело: `{ name, email, password, password_confirmation }`, ответ: `{ user, token }`
- `POST /api/login` — логин, тело: `{ email, password }`, ответ: `{ user, token }`
- `POST /api/logout` — разлогин (требует токен)
- `GET /api/me` — текущий пользователь (требует токен)

Баланс и операции (требуют токен):
- `GET /api/balance` — возвращает `{ success: true, balance: number }`
- `GET /api/operations` — список операций
  - Поддерживаемые параметры: `page`, `search`, `sort_direction` (`asc`|`desc`), `limit` (для последних N операций)
  - Ответ при серверной пагинации содержит секцию `pagination`
- `POST /api/operations` — создать операцию
  - Тело: `{ type: 'deposit'|'withdrawal', amount: number, description: string }`
  - Обработка асинхронная, в очереди; фронт ожидает изменение баланса и обновляет историю

SSE (опционально):
- `GET /sse/balance`, `GET /sse/operations` — сервер-сайд события (не подключены на фронте по умолчанию)

## Фоновая обработка и автообновление
- Создание операций уходит в очередь (`queue:work`). После успеха баланс меняется — фронт ждёт изменение (`balanceStore.waitForBalanceChange`) и обновляет последние операции.
- На страницах настроен polling для периодического обновления (5–10 секунд), чтобы данные были актуальными без ручного обновления.

## Production-сборка и деплой
1) Сборка фронтенда
```bash
npm run build
```
Артефакты кладутся в `public/build` (Vite manifest). Убедитесь, что Laravel отдаёт статику корректно.

2) Миграции/кэш
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

3) Веб-сервер
- Докер/NGINX/Apache должны указывать в `public/index.php` (DOCUMENT_ROOT — `public/`)
- Включите HTTPS/HTTP2 при необходимости

4) Очереди
- Запустите `php artisan queue:work` под процесс-менеджером (systemd/Supervisor/Docker)

5) Переменные окружения `.env`
- `APP_ENV=production`, `APP_DEBUG=false`, корректные `APP_URL`
- `QUEUE_CONNECTION=redis` (рекомендуется для прод)
- `DB_*`, `REDIS_*`

## Тесты и качество кода
- Тесты PHP: `composer test` (используется `phpunit`)
- Анализ и стиль:
  - `composer dev:all` — ide-helper, rector, phpstan, pint
  - JS/TS стиль: `npm run lint`, `npm run lint:fix`, `npm run format`

## Траблшутинг
- 401/redirect на `/login`:
  - Проверить, что `Authorization: Bearer <token>` добавляется (фронт делает это через axios interceptor)
  - Токен стирается при 401 автоматически — нужно войти заново
- Баланс/история не обновляются:
  - Убедиться, что запущен `php artisan queue:work`
  - Проверить миграции, таблицы `jobs`/`personal_access_tokens`
- Ошибки 500 при POST /api/operations:
  - Проверьте валидацию (type/amount/description)
  - Просмотрите логи (`storage/logs`) и `php artisan pail`
- Vite не подхватывается:
  - Запущен ли `npm run dev` (в dev) или выполнен ли `npm run build` (в prod)

## Полезные ссылки
- Laravel: https://laravel.com/docs
- Vue 3: https://vuejs.org
- Pinia: https://pinia.vuejs.org
- Vite: https://vitejs.dev

---

Если потребуется — можно включить SSE (уже есть эндпоинты), вынести отображение статусов операций в общий модуль, а также добавить интеграционные тесты для API. PR’ы приветствуются.
