<?php

namespace App\Policies;

use App\User;
use App\Deceased;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeceasedPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Deceased $deceased)
    {
        return $user->id === $deceased->user_id;
    }
}
