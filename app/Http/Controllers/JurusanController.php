<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JurusanController extends Controller
{
    protected $viewIndex = 'operator.jurusan_index';
    protected $viewCreate = 'operator.jurusan_form';
    protected $viewEdit = 'operator.jurusan_form';
    protected $viewShow = 'operator.jurusan_show';
    protected $routePrefix = 'jurusan';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jurusans = Jurusan::latest()->get();
        return view($this->viewIndex, [
            'jurusans' => $jurusans,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->viewCreate, [
            'jurusan' => new Jurusan(),
            'route' => $this->routePrefix . '.store',
            'method' => 'POST',
            'button' => 'SIMPAN',
            'title' => 'Form Tambah Jurusan',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route($this->routePrefix . '.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        Jurusan::create($request->all());
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jurusan $jurusan)
    {
        return view($this->viewShow, [
            'jurusan' => $jurusan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jurusan $jurusan)
    {
        return view($this->viewEdit, [
            'jurusan' => $jurusan,
            'route' => [$this->routePrefix . '.update', $jurusan->id],
            'method' => 'PUT',
            'button' => 'UPDATE',
            'title' => 'Form Edit Jurusan',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route($this->routePrefix . '.edit', $jurusan->id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $jurusan->update($request->all());
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jurusan $jurusan)
    {
        try {
            $jurusan->delete();
            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Data berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('error', 'Data gagal dihapus karena terhubung dengan data lain');
        }
    }
}