<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Выполняет операцию с блокировкой пользователя для предотвращения race conditions
     */
    public function executeWithUserLock(int $userId, callable $callback)
    {
        return DB::transaction(function () use ($userId, $callback) {
            // Блокируем пользователя для предотвращения одновременных операций
            $user = User::where('id', $userId)->lockForUpdate()->first();

            if (! $user) {
                throw new \Exception('User not found');
            }

            // Блокируем баланс пользователя
            $user->load(['balance' => function ($query): void {
                $query->lockForUpdate();
            }]);

            return $callback($user);
        });
    }

    /**
     * Выполняет операцию с повторными попытками при deadlock
     */
    public function executeWithRetry(callable $callback, int $maxRetries = 3)
    {
        $attempts = 0;

        while ($attempts < $maxRetries) {
            try {
                return $callback();
            } catch (\Illuminate\Database\QueryException $e) {
                // Если это deadlock, повторяем попытку
                if ($e->getCode() === '40001' || str_contains($e->getMessage(), 'Deadlock')) {
                    $attempts++;
                    if ($attempts >= $maxRetries) {
                        throw new \Exception('Transaction failed after '.$maxRetries.' attempts due to deadlock', $e->getCode(), $e);
                    }
                    // Небольшая задержка перед повторной попыткой
                    usleep(random_int(10000, 50000)); // 10-50ms

                    continue;
                }
                throw $e;
            }
        }

        return null;
    }
}
