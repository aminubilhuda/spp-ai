<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class WaliMuridProfileController extends Controller
{
    public function edit()
    {
        return view('wali.profile_form', [
            'model' => auth()->user(),
            'method' => 'PUT',
            'action' => 'wali.profile.update',
            'title' => 'Edit Profile',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Validasi input
        $requestData = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id,
            'nohp' => 'required|unique:users,nohp,'.$user->id,
            'password' => 'nullable|min:6',
        ]);

        // Jika password diisi, hash password baru
        if ($requestData['password'] ?? false) {
            $requestData['password'] = Hash::make($requestData['password']);
        } else {
            unset($requestData['password']);
        }

        // Update data user
        $user->update($requestData);
        
        return back()->with('success', 'Profile berhasil diupdate');
    }
} 