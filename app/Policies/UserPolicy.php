<?php

namespace MineStats\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MineStats\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view users.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function view(User $user)
    {
        return ($user->acl >= User::ACL_OWNER);
    }

    /**
     * Determine whether the user can create users.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return ($user->acl >= User::ACL_OWNER);
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param User $user
     * @param User $updated
     *
     * @return mixed
     */
    public function update(User $user, User $updated)
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param User $user
     * @param User $deleted
     *
     * @return mixed
     */
    public function delete(User $user, User $deleted)
    {
        // Unable to delete super admin or themself
        if ($deleted->id == 1 || $user->id == $deleted->id) {
            return false;
        }

        return $this->create($user);
    }
}
