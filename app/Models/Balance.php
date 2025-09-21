<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser(Builder $builder, int $userId): Builder
    {
        return $builder->where('user_id', $userId);
    }

    public function scopeWithLock(Builder $builder): Builder
    {
        return $builder->lockForUpdate();
    }

    // Безопасные методы работы с балансом
    public function updateAmount(float $amount): void
    {
        $this->update(['amount' => $amount]);
    }

    public function addAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        // Атомарное увеличение баланса
        $this->increment('amount', $amount);
    }

    public function subtractAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        // Проверяем достаточность средств перед списанием
        if ($this->amount < $amount) {
            throw new \Exception('Insufficient balance. Current: '.$this->amount.', Required: '.$amount);
        }

        // Атомарное уменьшение баланса
        $this->decrement('amount', $amount);
    }

    /**
     * Безопасное списание с проверкой баланса в одной транзакции
     */
    public function safeSubtract(float $amount): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        // Атомарное обновление с проверкой условия
        $updated = DB::table('balances')
            ->where('id', $this->id)
            ->where('amount', '>=', $amount)
            ->update([
                'amount' => DB::raw('amount - '.$amount),
                'updated_at' => now(),
            ]);

        if ($updated) {
            // Обновляем модель после успешного изменения
            $this->refresh();

            return true;
        }

        return false;
    }

    /**
     * Получить форматированный баланс
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2, ',', ' ').' ₽';
    }

    /**
     * Проверить достаточность средств
     */
    public function hasInsufficientFunds(float $amount): bool
    {
        return $this->amount < $amount;
    }
}
