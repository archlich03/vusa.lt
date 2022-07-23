<?php

namespace App\Policies;

use App\Models\DutyInstitution;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DutyInstitutionsPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->isAdminOrSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DutyInstitution  $dutyInstitution
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, DutyInstitution $dutyInstitution)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->isAdminOrSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DutyInstitution  $dutyInstitution
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, DutyInstitution $dutyInstitution)
    {
        return $user->padalinys()->id === $dutyInstitution->padalinys->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DutyInstitution  $dutyInstitution
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, DutyInstitution $dutyInstitution)
    {
        
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DutyInstitution  $dutyInstitution
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, DutyInstitution $dutyInstitution)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DutyInstitution  $dutyInstitution
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, DutyInstitution $dutyInstitution)
    {
        //
    }
}
