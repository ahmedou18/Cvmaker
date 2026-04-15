<?php

namespace App\Policies;

use App\Models\CoverLetter;
use App\Models\User;

class CoverLetterPolicy
{
    /**
     * Determine if the user can create a cover letter.
     * Requires the user's plan to have has_cover_letter enabled.
     */
    public function create(User $user): bool
    {
        return $user->plan?->has_cover_letter === true;
    }

    /**
     * Determine if the user can view their cover letters.
     */
    public function viewAny(User $user): bool
    {
        return $user->plan?->has_cover_letter === true;
    }

    /**
     * Determine if the user can view a specific cover letter.
     */
    public function view(User $user, CoverLetter $coverLetter): bool
    {
        return $user->id === $coverLetter->user_id
            && $user->plan?->has_cover_letter === true;
    }

    /**
     * Determine if the user can download a specific cover letter.
     */
    public function download(User $user, CoverLetter $coverLetter): bool
    {
        return $user->id === $coverLetter->user_id
            && $user->plan?->has_cover_letter === true;
    }
}
