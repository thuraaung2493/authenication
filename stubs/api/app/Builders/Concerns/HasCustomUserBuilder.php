<?php

namespace App\Builders\Concerns;

use App\Builders\UserBuilder;

trait HasCustomUserBuilder
{
    public static function query(): UserBuilder
    {
        /** @var UserBuilder */
        return parent::query();
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}
