## Задача
Реализовать тестовое задание **“Балансы пользователей”** на **Laravel (>=12)** + **Vue 3 SPA** (без Inertia) + **Sanctum**.

## Требования
- БД: MySQL/Postgres.
- Использовать миграции.
- DTO-подход (UserDTO, BalanceDTO, OperationDTO).
- Все операции с балансом — только через **транзакции**.
- Очереди (Jobs) для проведения операций (плюсом).
- Архитектура: **DTO → сервисы → контроллеры** (контроллеры максимально тонкие).

---

## Backend (Laravel)

### Таблицы
- **users**
    - id, name, email, password, timestamps
- **balances**
    - id, user_id (FK), amount decimal(15,2), timestamps
- **operations**
    - id, user_id (FK), type enum [credit, debit], amount decimal(15,2), description, created_at

### Сервисы
- `UserService` → регистрация пользователей через DTO.
- `BalanceService` → получение баланса, возврат в DTO.
- `OperationService` → проведение операций (начисление/списание) внутри `DB::transaction`, проверка «не уходить в минус».

### DTO
- `UserCreateDTO`, `UserDTO`
- `BalanceDTO`
- `OperationCreateDTO`, `OperationDTO`

### API роуты (`routes/api.php`)
- `POST /register`, `POST /login`, `POST /logout` (Sanctum).
- `GET /me` → текущий юзер.
- `GET /balance` → баланс + последние 5 операций.
- `GET /operations` → история (сортировка по дате, поиск по описанию).
- `POST /operations` → добавить операцию (credit/debit).

### Консольные команды
- `php artisan user:add {name} {email} {password}`
- `php artisan balance:operation {email} {type} {amount} {description}`

### Очереди (плюсом)
- Job `ProcessOperationJob` → вызывает `OperationService` внутри транзакции.

---

## Frontend (Vue 3 + Vite + Bootstrap 5)

### Auth
- Axios + Sanctum (`withCredentials: true`).
- Pinia для состояния юзера.
- Форма логина/регистрации.

### Страницы
- `/login` → форма входа.
- `/` (dashboard) → баланс + последние 5 операций (автообновление каждые T секунд через axios).
- `/operations` → таблица операций (сортировка по дате + поиск по описанию).

### Компоненты
- `BalanceCard.vue` → отображает баланс.
- `OperationsTable.vue` → список операций с фильтрацией/сортировкой.

---

## Важные детали для Copilot
- Всегда использовать `DB::transaction` для изменения баланса.
- При списании проверять, что баланс не уходит в минус.
- Сервисы возвращают **DTO**, а не Eloquent-модели.
- Контроллеры: принимают DTO из реквеста → вызывают сервис → возвращают JSON.
- Если Copilot останавливает генерацию кода — он обязан продолжить ровно с того места, где остановился, без повторов и пропусков.

---

## Пример кода (OperationService)

```php
namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\DTO\OperationDTO;
use App\Models\Balance;
use App\Models\Operation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OperationService
{
    public function create(OperationCreateDTO $dto): OperationDTO
    {
        return DB::transaction(function () use ($dto) {
            $balance = Balance::where('user_id', $dto->userId)->lockForUpdate()->first();

            if (!$balance) {
                throw new RuntimeException("Баланс пользователя не найден");
            }

            if ($dto->type === 'debit' && $balance->amount < $dto->amount) {
                throw new RuntimeException("Недостаточно средств");
            }

            // обновляем баланс
            $balance->amount = $dto->type === 'credit'
                ? $balance->amount + $dto->amount
                : $balance->amount - $dto->amount;
            $balance->save();

            // создаём операцию
            $operation = Operation::create([
                'user_id'     => $dto->userId,
                'type'        => $dto->type,
                'amount'      => $dto->amount,
                'description' => $dto->description,
            ]);

            return new OperationDTO(
                $operation->id,
                $operation->user_id,
                $operation->type,
                $operation->amount,
                $operation->description,
                $operation->created_at
            );
        });
    }
}
