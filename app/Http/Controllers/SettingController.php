<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getInstansiSettings();
        
        return view('operator.setting_form', compact('settings'));
    }

    public function create()
    {
        $settings = Setting::getInstansiSettings();
        
        return view('operator.setting_form', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'email_instansi' => 'required|email|max:255',
            'nomor_wa_instansi' => 'required|string|max:20',
            'alamat_instansi' => 'required|string|max:500',
            'logo_instansi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ], [
            'nama_instansi.required' => 'Nama instansi harus diisi',
            'nama_instansi.max' => 'Nama instansi maksimal 255 karakter',
            'email_instansi.required' => 'Email instansi harus diisi',
            'email_instansi.email' => 'Format email tidak valid',
            'email_instansi.max' => 'Email instansi maksimal 255 karakter',
            'nomor_wa_instansi.required' => 'Nomor WhatsApp instansi harus diisi',
            'nomor_wa_instansi.max' => 'Nomor WhatsApp maksimal 20 karakter',
            'alamat_instansi.required' => 'Alamat instansi harus diisi',
            'alamat_instansi.max' => 'Alamat instansi maksimal 500 karakter'
        ]);

        $settings = [
            'nama_instansi' => $request->nama_instansi,
            'email_instansi' => $request->email_instansi,
            'nomor_wa_instansi' => $request->nomor_wa_instansi,
            'alamat_instansi' => $request->alamat_instansi
        ];

        // Proses upload logo jika ada
        if ($request->hasFile('logo_instansi')) {
            $file = $request->file('logo_instansi');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Hapus logo lama jika ada
            $oldSettings = Setting::getInstansiSettings();
            if ($oldSettings->logo_instansi) {
                Storage::disk('public')->delete($oldSettings->logo_instansi);
            }
            
            // Upload logo baru
            $file->storeAs('', $filename, 'public');
            $settings['logo_instansi'] = $filename;
        }

        // Simpan ke database
        Setting::saveInstansiSettings($settings);

        return redirect()->back()->with('success', 'Pengaturan instansi berhasil disimpan!');
    }
}
