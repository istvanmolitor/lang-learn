<?php

namespace App\Repositories\Eloquent;

use App\Models\UserLanguage;
use App\Repositories\Contracts\UserLanguageRepositoryInterface;

/**
 * @extends EloquentRepository<UserLanguage>
 */
class EloquentUserLanguageRepository extends EloquentRepository implements UserLanguageRepositoryInterface
{
    protected function modelClass(): string
    {
        return UserLanguage::class;
    }
}
