<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (!$user->wallet()->exists()) {
            $user->wallet()->create([
                'balance' => 0,
                'status' => 'active',
            ]);
        }
    }
}
