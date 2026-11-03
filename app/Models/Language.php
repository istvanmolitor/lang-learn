<?php

namespace App\Models;

use Database\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $native_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'native_name'])]
class Language extends Model
{
    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    /**
     * @return HasMany<User, $this>
     */
    public function nativeSpeakers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<User, $this, UserLanguage>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_languages')
            ->using(UserLanguage::class)
            ->withPivot('level')
            ->withTimestamps();
    }
}
