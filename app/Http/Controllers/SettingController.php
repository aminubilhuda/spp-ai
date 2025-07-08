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
            'logo_instansi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'nama_penanggung_jawab' => 'nullable|string|max:255',
            'nama_jabatan' => 'nullable|string|max:255',
            'ttd_penanggung_jawab' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ], [
            'nama_instansi.required' => 'Nama instansi harus diisi',
            'nama_instansi.max' => 'Nama instansi maksimal 255 karakter',
            'email_instansi.required' => 'Email instansi harus diisi',
            'email_instansi.email' => 'Format email tidak valid',
            'email_instansi.max' => 'Email instansi maksimal 255 karakter',
            'nomor_wa_instansi.required' => 'Nomor WhatsApp instansi harus diisi',
            'nomor_wa_instansi.max' => 'Nomor WhatsApp maksimal 20 karakter',
            'alamat_instansi.required' => 'Alamat instansi harus diisi',
            'alamat_instansi.max' => 'Alamat instansi maksimal 500 karakter',
            'nama_penanggung_jawab.max' => 'Nama penanggung jawab maksimal 255 karakter',
            'nama_jabatan.max' => 'Nama jabatan maksimal 255 karakter'
        ]);

        $settings = [
            'nama_instansi' => $request->nama_instansi,
            'email_instansi' => $request->email_instansi,
            'nomor_wa_instansi' => $request->nomor_wa_instansi,
            'alamat_instansi' => $request->alamat_instansi,
            'nama_penanggung_jawab' => $request->nama_penanggung_jawab,
            'nama_jabatan' => $request->nama_jabatan
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

        // Proses upload TTD jika ada
        if ($request->hasFile('ttd_penanggung_jawab')) {
            $file = $request->file('ttd_penanggung_jawab');
            $filename = 'ttd_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Hapus TTD lama jika ada
            $oldSettings = Setting::getInstansiSettings();
            if ($oldSettings->ttd_penanggung_jawab) {
                Storage::disk('public')->delete($oldSettings->ttd_penanggung_jawab);
            }
            
            // Upload TTD baru ke folder ttd/
            $file->storeAs('ttd/', $filename, 'public');
            $settings['ttd_penanggung_jawab'] = 'ttd/' . $filename;
        }

        // Simpan ke database
        Setting::saveInstansiSettings($settings);

        return redirect()->back()->with('success', 'Pengaturan instansi berhasil disimpan!');
    }
}
