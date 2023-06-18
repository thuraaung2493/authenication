<?php

declare(strict_types=1);

namespace Thuraaung\Authentication\Http\Requests\Concerns;

use Thuraaung\MakeFiles\Contracts\DataObjectContract;

interface PayloadRequestContract
{
    public function payload(): DataObjectContract;
}
