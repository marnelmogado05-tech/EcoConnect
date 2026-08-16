<?php

namespace App\Policies;

use App\Models\User;

/**
 * Access rules for user records, principally the uploaded ID card.
 */
class UserPolicy
{
    /**
     * View someone's ID card image.
     *
     * A government ID is the most sensitive thing this application stores. Only the
     * person it belongs to and an administrator reviewing a registration may see it.
     */
    public function viewIdCard(User $user, User $subject): bool
    {
        return $user->id === $subject->id || $user->role === 'admin';
    }

    /**
     * Suspend, reinstate, or reset the password of an account.
     */
    public function manage(User $user, User $subject): bool
    {
        return $user->role === 'admin' && $user->id !== $subject->id;
    }
}
