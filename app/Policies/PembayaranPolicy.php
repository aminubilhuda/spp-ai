<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PembayaranPolicy
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
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if (in_array($user->akses, ['admin', 'operator'])) {
            return true;
        }

        // Wali can only view their own payments
        if ($user->akses === 'wali') {
            return $pembayaran->wali_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->akses, ['admin', 'operator', 'wali']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pembayaran $pembayaran): bool
    {
        // Only admin and operator can update payments
        return in_array($user->akses, ['admin', 'operator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pembayaran $pembayaran): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pembayaran $pembayaran): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pembayaran $pembayaran): bool
    {
        return $user->akses === 'admin';
    }

    /**
     * Determine whether the user can confirm the payment.
     */
    public function confirm(User $user, Pembayaran $pembayaran): bool
    {
        return in_array($user->akses, ['admin', 'operator']);
    }
}
