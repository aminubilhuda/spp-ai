<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BankController extends Controller
{
    /**
     * Store a newly created bank.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sandi_bank' => 'required|string|max:20|unique:banks,sandi_bank',
                'nama_bank' => 'required|string|max:255',
            ]);

            $bank = Bank::create([
                'sandi_bank' => $request->sandi_bank,
                'nama_bank' => $request->nama_bank,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank berhasil ditambahkan',
                'data' => [
                    'id' => $bank->id,
                    'sandi_bank' => $bank->sandi_bank,
                    'nama_bank' => $bank->nama_bank,
                ]
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $message = 'Validasi gagal: ';
            
            if (isset($errors['sandi_bank'])) {
                $message .= 'Kode bank sudah ada dalam database.';
            } elseif (isset($errors['nama_bank'])) {
                $message .= 'Nama bank tidak boleh kosong.';
            } else {
                $message .= implode(', ', array_flatten($errors));
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan bank: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all banks for dropdown.
     */
    public function getBanks(): JsonResponse
    {
        try {
            $banks = Bank::orderBy('nama_bank')->get();
            
            return response()->json([
                'success' => true,
                'data' => $banks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data bank: ' . $e->getMessage()
            ], 500);
        }
    }
} 