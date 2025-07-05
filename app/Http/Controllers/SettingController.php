<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
            'alamat_instansi' => 'required|string|max:500'
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

        // Simpan ke database
        Setting::saveInstansiSettings($settings);

        return redirect()->back()->with('success', 'Pengaturan instansi berhasil disimpan!');
    }
}
