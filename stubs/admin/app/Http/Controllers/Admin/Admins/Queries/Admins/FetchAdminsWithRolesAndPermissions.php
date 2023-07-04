<?php

declare(strict_types=1);

namespace App\Queries\Admins;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;

final readonly class FetchAdminsWithRolesAndPermissions
{
    public function handle(): Builder
    {
        return Admin::query()->with([
            'roles',
            'roles.permissions',
        ]);
    }
}
