<?php

namespace App\Http\Controllers;

use App\Models\Biaya as Model;
use App\Http\Requests\StoreBiayaRequest;
use App\Http\Requests\UpdateBiayaRequest;

class BiayaController extends Controller
{
    private $viewIndex = 'biaya_index';
    private $viewCreate = 'biaya_form';
    private $viewEdit = 'biaya_form';
    private $viewShow = 'biaya_show';
    private $routePrefix = 'biaya';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('operator.' . $this->viewIndex, [
            'models' => Model::with('user')->latest()->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Biaya',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'model' => new Model(),
            'method' => 'POST',
            'action' => $this->routePrefix . '.store',
            'title' => 'Form Data Biaya',
            'button' => 'SIMPAN',
        ];
        return view('operator.' . $this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBiayaRequest $request)
    {
        Model::create($request->validated());
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Biaya berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = [
            'model' => Model::findOrFail($id),
            'title' => 'Detail Biaya',
            'routePrefix' => $this->routePrefix,
        ];
        return view('operator.' . $this->viewShow, $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = [
            'model' => Model::findOrFail($id),
            'method' => 'PUT',
            'action' => $this->routePrefix . '.update',
            'id' => $id,
            'title' => 'Edit Biaya',
            'button' => 'UPDATE',
        ];
        return view('operator.' . $this->viewEdit, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBiayaRequest $request, string $id)
    {
        Model::findOrFail($id)->update($request->validated());
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Biaya berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Model::findOrFail($id);
        $model->delete();

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Biaya berhasil dihapus');
    }
}