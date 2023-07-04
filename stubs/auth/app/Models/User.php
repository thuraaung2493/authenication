<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Language;
use App\Enums\LoginType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static UserFactory factory(callable|array|int|null $count, callable|array $state)
 */
final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'login_type' => LoginType::class,
        'language' => Language::class,
    ];

    public function revokeTokens(): void
    {
        $this->tokens()->delete();
    }
}
