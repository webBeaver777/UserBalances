<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property float $amount
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class Operation extends Model
{
    use HasFactory;

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'status',
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

    public function scopeByType(Builder $builder, string $type): Builder
    {
        return $builder->where('type', $type);
    }

    public function scopeByStatus(Builder $builder, string $status): Builder
    {
        return $builder->where('status', $status);
    }

    public function scopeRecent(Builder $builder): Builder
    {
        return $builder->orderBy('created_at', 'desc');
    }

    public function scopeSearchByDescription(Builder $builder, string $search): Builder
    {
        return $builder->where('description', 'like', "%{$search}%");
    }

    public function scopeCompleted(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUS_FAILED);
    }

    // Status Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    // Type Checking Methods
    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_DEPOSIT;
    }

    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_WITHDRAWAL;
    }

    // Status Checking Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    // Business Logic Methods
    public function getFormattedAmount(): string
    {
        $prefix = $this->isDeposit() ? '+' : '-';
        return $prefix . number_format($this->amount, 2, ',', ' ') . ' ₽';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'В обработке',
            self::STATUS_COMPLETED => 'Выполнено',
            self::STATUS_FAILED => 'Ошибка',
            default => $this->status
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_DEPOSIT => 'Пополнение',
            self::TYPE_WITHDRAWAL => 'Списание',
            default => $this->type
        };
    }
}
