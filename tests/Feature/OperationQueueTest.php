<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_async_operation_is_processed_by_queue()
    {
        config(['queue.default' => 'database']);
        $user = User::factory()->create();
        $balance = Balance::create(['user_id' => $user->id, 'amount' => 1000]);
        $this->actingAs($user);

        // Отправляем операцию асинхронно
        $response = $this->postJson('/api/operations?async=1', [
            'type' => 'debit',
            'amount' => 200,
            'description' => 'Async debit',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'queued']);

        // Операция не должна появиться сразу
        $this->assertDatabaseMissing('operations', [
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 200,
            'description' => 'Async debit',
        ]);
        $balance->refresh();
        $this->assertEquals(1000, $balance->amount);

        // Запускаем обработку очереди
        $this->artisan('queue:work --once');

        // Операция должна появиться, баланс обновиться
        $this->assertDatabaseHas('operations', [
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 200,
            'description' => 'Async debit',
        ]);
        $balance->refresh();
        $this->assertEquals(800, $balance->amount);
    }
}
