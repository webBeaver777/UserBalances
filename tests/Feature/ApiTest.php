<?php

namespace Tests\Feature;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_and_me(): void
    {
        // Регистрация
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email']);

        // Логин
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Успешно']);

        // Получение текущего пользователя
        $user = User::first();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/me');
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email']);
    }

    public function test_balance_and_operations(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        Balance::create(['user_id' => $user->id, 'amount' => 100]);
        $this->actingAs($user, 'sanctum');

        // Получение баланса
        $response = $this->getJson('/api/balance');
        $response->assertStatus(200)
            ->assertJsonStructure(['balance' => ['userId', 'amount'], 'operations']);

        // Получение операций
        $response = $this->getJson('/api/operations');
        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    public function test_add_operation_and_balance_update(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        Balance::create(['user_id' => $user->id, 'amount' => 100]);
        $this->actingAs($user, 'sanctum');

        // Добавление операции (списание)
        $response = $this->postJson('/api/operations', [
            'type' => 'debit',
            'amount' => 50,
            'description' => 'Покупка',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'userId', 'type', 'amount', 'description', 'createdAt']);
        $this->assertEquals(50, Balance::where('user_id', $user->id)->first()->amount);

        // Добавление операции (пополнение)
        $response = $this->postJson('/api/operations', [
            'type' => 'credit',
            'amount' => 30,
            'description' => 'Пополнение',
        ]);
        $response->assertStatus(200);
        $this->assertEquals(80, Balance::where('user_id', $user->id)->first()->amount);
    }

    public function test_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $testResponse = $this->postJson('/api/logout');
        $testResponse->assertStatus(200)
            ->assertJson(['message' => 'Выход выполнен']);
    }
}
