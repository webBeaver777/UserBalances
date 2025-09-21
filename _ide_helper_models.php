<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Balance withLock()
 */
	class Balance extends \Eloquent {}
}

namespace App\Models{
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation searchByDescription(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operation whereUserId($value)
 */
	class Operation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Balance|null $balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Operation> $operations
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $operations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User byEmail(string $email)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withBalance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withRecentOperations(int $limit = 10)
 */
	class User extends \Eloquent {}
}

