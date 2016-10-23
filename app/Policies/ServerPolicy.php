<?php

namespace MineStats\Policies;

use MineStats\Models\User;
use MineStats\Models\Server;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create servers.
     *
     * @param  \MineStats\Models\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return ($user->acl >= User::ACL_ADMIN);
    }

    /**
     * Determine whether the user can update servers.
     *
     * @param  \MineStats\Models\User $user
     *
     * @return mixed
     */
    public function update(User $user)
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can delete servers.
     *
     * @param  \MineStats\Models\User $user
     *
     * @return mixed
     */
    public function delete(User $user)
    {
        return $this->create($user);
    }
}
