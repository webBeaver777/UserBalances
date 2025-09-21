<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Balance|null $balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Operation> $operations
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function balance(): HasOne
    {
        // Гарантируем объект с amount=0 по умолчанию, чтобы избежать nullsafe в вычислениях
        return $this->hasOne(Balance::class)->withDefault([
            'amount' => 0,
        ]);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    // Scopes
    public function scopeByEmail(Builder $builder, string $email): Builder
    {
        return $builder->where('email', $email);
    }

    public function scopeWithBalance(Builder $builder): Builder
    {
        return $builder->with('balance');
    }

    public function scopeWithRecentOperations(Builder $builder, int $limit = 10): Builder
    {
        return $builder->with(['operations' => function ($query) use ($limit): void {
            $query->orderBy('created_at', 'desc')->limit($limit);
        }]);
    }

    // Business Logic Methods
    public function getBalanceAmount(): float
    {
        // Благодаря withDefault() баланс всегда объект, поэтому nullsafe не требуется
        return (float) $this->balance->amount;
    }

    public function ensureBalance(): Balance
    {
        if (! $this->balance) {
            $this->balance()->create(['amount' => 0]);
            $this->load('balance');
        }

        return $this->balance;
    }

    public function hasInsufficientBalance(float $amount): bool
    {
        return $this->getBalanceAmount() < $amount;
    }

    public function getRecentOperations(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->operations()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
