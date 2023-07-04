<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Admins;

use App\Http\Resources\Admin\AdminResource;
use App\Queries\Admins\FetchAdminsWithRolesAndPermissions;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

final class IndexController
{
    public function __construct(
        private readonly FetchAdminsWithRolesAndPermissions $query,
    ) {
    }

    public function __invoke(): Responsable
    {
        return new PaginatedResourceResponse(
            resource: AdminResource::collection(
                resource: $this->query->handle()->paginate(),
            ),
        );
    }
}
