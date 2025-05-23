<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TagihanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->akses, ['admin', 'operator', 'wali']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tagihan $tagihan): bool
    {
        if (in_array($user->akses, ['admin', 'operator'])) {
            return true;
        }

        // Wali can only view tagihan for their students
        if ($user->akses === 'wali') {
            return $tagihan->siswa->wali_id === $user->id;
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
    public function update(User $user, Tagihan $tagihan): bool
    {
        return in_array($user->akses, ['admin', 'operator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tagihan $tagihan): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tagihan $tagihan): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tagihan $tagihan): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can create a payment.
     */
    public function createPayment(User $user, Tagihan $tagihan): bool
    {
        if (in_array($user->akses, ['admin', 'operator'])) {
            return true;
        }

        // Wali can only create payments for their students' tagihan
        if ($user->akses === 'wali') {
            return $tagihan->siswa->wali_id === $user->id;
        }

        return false;
    }
}
