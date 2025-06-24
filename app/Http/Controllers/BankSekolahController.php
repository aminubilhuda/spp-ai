<?php

namespace App\Http\Controllers;

use App\Models\BankSekolah;
use App\Models\Bank;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankSekolahRequest;
use App\Http\Requests\UpdateBankSekolahRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankSekolahController extends Controller
{
    private $viewIndex = 'bank_sekolah_index';
    private $viewCreate = 'bank_sekolah_form';
    private $viewEdit = 'bank_sekolah_form';
    private $viewShow = 'bank_sekolah_show';
    private $routePrefix = 'bank-sekolah';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('operator.' . $this->viewIndex, [
            'models' => BankSekolah::latest()->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Bank Sekolah',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'model' => new BankSekolah(),
            'method' => 'POST',
            'action' => $this->routePrefix . '.store',
            'title' => 'Form Data Bank Sekolah',
            'button' => 'SIMPAN',
            'banks' => Bank::orderBy('nama_bank')->get(),
            'selectedBankId' => null,
        ];
        return view('operator.' . $this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBankSekolahRequest $request)
    {
        BankSekolah::create($request->validated());
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Bank Sekolah berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = [
            'model' => BankSekolah::findOrFail($id),
            'title' => 'Detail Bank Sekolah',
            'routePrefix' => $this->routePrefix,
        ];
        return view('operator.' . $this->viewShow, $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model = BankSekolah::findOrFail($id);
        $banks = Bank::orderBy('nama_bank')->get();
        
        // Find the selected bank based on kode_bank
        $selectedBank = $banks->where('sandi_bank', $model->kode_bank)->first();
        
        $data = [
            'model' => $model,
            'method' => 'PUT',
            'action' => $this->routePrefix . '.update',
            'id' => $id,
            'title' => 'Edit Bank Sekolah',
            'button' => 'UPDATE',
            'banks' => $banks,
            'selectedBankId' => $selectedBank ? $selectedBank->id : null,
        ];
        return view('operator.' . $this->viewEdit, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankSekolahRequest $request, string $id)
    {
        BankSekolah::findOrFail($id)->update($request->validated());
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Bank Sekolah berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = BankSekolah::findOrFail($id);
        $model->delete();

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Bank Sekolah berhasil dihapus');
    }
}