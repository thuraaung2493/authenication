<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @method static AdminFactory factory(callable|array|int|null $count, callable|array $state)
 */
final class Admin extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;

    protected $guarded = [];
}
