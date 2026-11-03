<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $language_id
 * @property int|null $selected_language_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'language_id', 'selected_language_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The user's native language.
     *
     * @return BelongsTo<Language, $this>
     */
    public function nativeLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * The language the user is currently learning.
     *
     * @return BelongsTo<Language, $this>
     */
    public function selectedLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'selected_language_id');
    }

    /**
     * The languages the user knows, with their proficiency level.
     *
     * @return BelongsToMany<Language, $this, UserLanguage>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'user_languages')
            ->using(UserLanguage::class)
            ->withPivot('level')
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
