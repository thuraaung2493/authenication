<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\Concerns\HasRender;
use Exception;

final class EmailLoginException extends Exception
{
    use HasRender;

    public const TITLE = 'Email Login Failed!';
}
