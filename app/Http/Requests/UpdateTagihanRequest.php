<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagihanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'user_id' => 'required|exists:users,id',
            'angkatan' => 'nullable|numeric',
            'jurusan' => 'nullable|numeric', 
            'kelas' => 'nullable|string',
            'tanggal_tagihan' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'nama_biaya' => 'required|string',
            'jumlah_biaya' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'denda' => 'required|string',
            'status' => 'required|in:baru,angsur,lunas,belum_lunas'
        ];
    }
}
