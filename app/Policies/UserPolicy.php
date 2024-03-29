<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $user
     * @param  \App\user  $pUser
     * @return mixed
     */
    public function update(User $user, User $pUser)
    {
        return $user->id === $pUser->id;
    }
}
