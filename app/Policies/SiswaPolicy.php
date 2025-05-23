<?php

namespace App\Policies;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SiswaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->akses, ['admin', 'operator']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Siswa $siswa): bool
    {
        if (in_array($user->akses, ['admin', 'operator'])) {
            return true;
        }
        
        // Wali can only view their own students
        if ($user->akses === 'wali') {
            return $siswa->wali_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->akses, ['admin', 'operator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Siswa $siswa): bool
    {
        return in_array($user->akses, ['admin', 'operator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Siswa $siswa): bool
    {
        return in_array($user->akses, ['admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Siswa $siswa): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Siswa $siswa): bool
    {
        return $user->akses === 'admin';
    }
}
