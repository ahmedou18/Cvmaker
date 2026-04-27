<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;     // أضف هذا السطر
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

// ... باقي الـ use statements (User, Template, PersonalDetail, Experience, Education, Skill, Language, Reference, Hobby)

class Resume extends Model
{
    // ... الحقول والـ casts ...

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

    public function decrementNameChanges(): void
    {
        if ($this->name_changes_left > 0) {
            $this->decrement('name_changes_left');
            if ($this->name_changes_left == 0) {
                $this->update(['is_name_locked' => true]);
            }
        }
    }
}