<?php

namespace App\Http\Requests;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;

class StoreBankSekolahRequest extends FormRequest
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
            'bank_id' => 'required|exists:banks,id',
            'kode_bank' => 'required|string|max:20|unique:bank_sekolahs,kode_bank',
            'nama_bank' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'atas_nama' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'bank_id.required' => 'Pilih bank wajib diisi',
            'bank_id.exists' => 'Bank yang dipilih tidak valid',
            'kode_bank.required' => 'Kode bank wajib diisi',
            'kode_bank.unique' => 'Kode bank sudah digunakan',
            'nama_bank.required' => 'Nama bank wajib diisi',
            'no_rekening.required' => 'Nomor rekening wajib diisi',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('bank_id') && $this->bank_id) {
            $bank = Bank::find($this->bank_id);
            if ($bank) {
                $this->merge([
                    'kode_bank' => $bank->sandi_bank,
                    'nama_bank' => $bank->nama_bank,
                ]);
            }
        }
    }
}
