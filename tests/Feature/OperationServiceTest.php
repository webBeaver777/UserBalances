<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\OperationCreateDTO;
use App\Models\Balance;
use App\Models\User;
use App\Services\OperationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_operation_and_updates_balance()
    {
        $user = User::factory()->create();
        $balance = Balance::create(['user_id' => $user->id, 'amount' => 1000]);

        $dto = new OperationCreateDTO(
            userId: $user->id,
            type: 'debit',
            amount: 200,
            description: 'Test debit'
        );

        $service = app(OperationService::class);
        $operation = $service->store($dto);

        $this->assertDatabaseHas('operations', [
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 200,
            'description' => 'Test debit',
        ]);
        $balance->refresh();
        $this->assertEquals(800, $balance->amount);
        $this->assertEquals($operation->user_id, $user->id);
    }
}
