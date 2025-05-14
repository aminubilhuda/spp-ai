<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiswaRequest extends FormRequest
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
            'nama' => 'required',
            'nisn' => 'required|unique:siswas,nisn,'.$this->siswa,
            'nis' => 'required|unique:siswas,nis,'.$this->siswa,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'jenis_kelamin' => 'required',
            'jurusan_id' => 'required|exists:jurusans,id',
            'kelas' => 'required',
            'angkatan' => 'required|numeric',
            'wali_id' => 'nullable|exists:users,id',
            'wali_status' => 'nullable',
        ];
    }
}