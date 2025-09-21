<?php

namespace App\Services;

use App\Models\Operation;
use Illuminate\Support\Facades\Redis;

class RealtimeService
{
    private function pushToList(string $key, string $payload, int $ttl = 3600): void
    {
        // LPUSH + ограничиваем длину и TTL
        Redis::lpush($key, $payload);
        Redis::ltrim($key, 0, 99); // храним последние 100
        Redis::expire($key, $ttl);
    }

    public function publishBalanceUpdated(int $userId, float $amount): void
    {
        $payload = json_encode([
            'user_id' => $userId,
            'amount' => $amount,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Канал pub/sub (не обязателен, но пусть остаётся)
        Redis::publish("balance.updated.{$userId}", $payload);
        // Очередь сообщений для BRPOP в SSE
        $this->pushToList("sse:balance:{$userId}", $payload);
    }

    public function publishOperationsUpdated(int $userId, int $limit = 5): void
    {
        $operations = Operation::forUser($userId)->recent()->limit($limit)->get(['id', 'type', 'amount', 'description', 'created_at', 'status']);
        $payload = json_encode([
            'user_id' => $userId,
            'updated' => true,
            'items' => $operations,
            'timestamp' => now()->toIso8601String(),
        ]);

        Redis::publish("operations.updated.{$userId}", $payload);
        $this->pushToList("sse:operations:{$userId}", $payload);
    }
}
