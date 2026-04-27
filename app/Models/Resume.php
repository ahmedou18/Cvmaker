<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Resume extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'uuid',
        'title',
        'is_published',
        'settings',
        'extra_sections',
        'resume_language',
        'name_changes_left',
        'is_name_locked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_published' => 'boolean',
        'extra_sections' => 'array',
        'is_name_locked' => 'boolean',
        'name_changes_left' => 'integer',
    ];

    /**
     * Boot the model to automatically generate UUID when creating.
     */
    protected static function booted(): void
    {
        static::creating(function ($resume) {
            if (empty($resume->uuid)) {
                $resume->uuid = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function personalDetail(): HasOne
    {
        return $this->hasOne(PersonalDetail::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function hobbies(): HasMany
    {
        return $this->hasMany(Hobby::class)->orderBy('sort_order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class)->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Decrement the remaining name changes and lock if reaches zero.
     */
    public function decrementNameChanges(): void
    {
        if ($this->name_changes_left > 0) {
            $this->decrement('name_changes_left');
            if ($this->name_changes_left === 0) {
                $this->update(['is_name_locked' => true]);
            }
        }
    }
}