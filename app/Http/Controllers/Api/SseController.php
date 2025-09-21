<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseController
{
    private function extractTokenString(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }
        // Если заголовок Authorization: Bearer <token>
        if (str_starts_with($raw, 'Bearer ')) {
            $raw = substr($raw, 7);
        }
        $raw = trim($raw);
        // Токен Sanctum имеет вид id|plaintext. В БД хранится sha256(plaintext)
        if (str_contains($raw, '|')) {
            [, $plain] = explode('|', $raw, 2);

            return $plain;
        }

        return $raw;
    }

    private function authenticate(Request $request): ?int
    {
        // Приоритет — Authorization header, затем query ?token=
        $rawHeader = $request->header('Authorization');
        $rawQuery = $request->query('token');
        $plain = $this->extractTokenString($rawHeader) ?? $this->extractTokenString($rawQuery);
        if (! $plain) {
            return null;
        }
        $hashed = hash('sha256', $plain);
        $row = PersonalAccessToken::where('token', $hashed)->first();

        return $row?->tokenable_id;
    }

    private function streamList(string $listKey, string $eventName): StreamedResponse
    {
        $streamedResponse = new StreamedResponse(function () use ($listKey, $eventName): void {
            ignore_user_abort(true);
            @ob_end_flush();
            @ob_implicit_flush(true);

            $lastPing = time();
            echo ": open\n\n"; // initial comment
            @flush();

            while (! connection_aborted()) {
                // BRPOP с таймаутом 25 секунд
                try {
                    $res = Redis::brpop([$listKey], 25);
                } catch (\RedisException) {
                    // короткая задержка и повтор — восстановление при read error
                    usleep(200000); // 200ms

                    continue;
                }

                if ($res && is_array($res) && count($res) === 2) {
                    [, $payload] = $res;
                    echo 'event: '.$eventName."\n";
                    echo 'data: '.$payload."\n\n";
                    @flush();
                }

                // keepalive каждые ~30 секунд
                if (time() - $lastPing >= 30) {
                    echo ": keepalive\n\n";
                    $lastPing = time();
                    @flush();
                }
            }
        });

        $streamedResponse->headers->set('Content-Type', 'text/event-stream');
        $streamedResponse->headers->set('Cache-Control', 'no-cache');
        $streamedResponse->headers->set('X-Accel-Buffering', 'no');

        return $streamedResponse;
    }

    public function balance(Request $request): StreamedResponse
    {
        $userId = $this->authenticate($request);
        if (! $userId) {
            abort(401);
        }

        return $this->streamList("sse:balance:{$userId}", 'balance');
    }

    public function operations(Request $request): StreamedResponse
    {
        $userId = $this->authenticate($request);
        if (! $userId) {
            abort(401);
        }

        return $this->streamList("sse:operations:{$userId}", 'operations');
    }
}
