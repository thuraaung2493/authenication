<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OtpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static OtpFactory factory(callable|array|int|null $count, callable|array $state)
 */
final class Otp extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}
