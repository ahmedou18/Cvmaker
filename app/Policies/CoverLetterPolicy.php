<?php

namespace App\Policies;

use App\Models\CoverLetter;
use App\Models\User;

class CoverLetterPolicy
{
    public function create(User $user): bool
    {
        return $user->plan?->has_cover_letter === true && $user->cover_letters_balance > 0;
    }

    public function viewAny(User $user): bool
    {
        return $user->plan?->has_cover_letter === true;
    }

    public function view(User $user, CoverLetter $coverLetter): bool
    {
        return $user->id === $coverLetter->user_id && $user->plan?->has_cover_letter === true;
    }

    public function download(User $user, CoverLetter $coverLetter): bool
    {
        return $user->id === $coverLetter->user_id && $user->plan?->has_cover_letter === true;
    }
}