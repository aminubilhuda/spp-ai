<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User as Model;
use Illuminate\Support\Facades\Hash;
 
//refactor code
class WaliController extends Controller
{
    private $viewIndex = 'user_index';
    private $viewCreate = 'user_form';
    private $viewEdit = 'user_form';
    private $viewShow = 'user_form';
    private $routePrefix = 'wali';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view ('operator.'.$this->viewIndex, [
            'models' => Model::where('akses', 'wali')
            ->latest()
            ->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Wali Murid',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // tambah user
        $data = [
            'model' => new Model(),
            'method' => 'POST',
            'action' => $this->routePrefix.'.store',
            'title' => 'Form Data Wali',
            'button' => 'SIMPAN',
        ];
        return view('operator.'.$this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validasi data
        $requestData = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
            'nohp' => 'required|unique:users',
        ]);
        // tambah user
        $requestData['password'] = Hash::make($requestData['password']);
        $requestData['email_verified_at'] = now();
        $requestData['akses'] = 'wali';
        Model::create($requestData);
        $request->session()->flash('success', 'User berhasil ditambahkan');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $wali = Model::where('akses', 'wali')->findOrFail($id);
        $data = [
            'model' => $wali,
            'title' => 'Detail Wali Murid',
            'routePrefix' => $this->routePrefix,
            'siswaList' => \App\Models\Siswa::with(['jurusan', 'user'])->where('wali_id', $id)->get()
        ];
        return view('operator.wali_detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // edit user
        $data = [
            'model' => Model::findOrFail($id) ,
            'method' => 'PUT',
            'action' => $this->routePrefix.'.update',
            'id' => $id,
            'title' => 'Edit Wali',
            'button' => 'UPDATE',
        ];
        return view('operator.'.$this->viewEdit, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // validasi data
        $requestData = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$id,
            'password' => 'nullable',
            'nohp' => 'required|unique:users,nohp,'.$id,
        ]);       
        if ($requestData['password'] == null) {
          unset($requestData['password']);
        }else{
          $requestData['password'] = Hash::make($requestData['password']);
        }
        Model::findOrFail($id)->update($requestData);
        $request->session()->flash('success', 'User berhasil diupdate');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // delete wali
        $model =Model::where('akses', 'wali')->findOrFail($id);

        $model->delete();

        session()->flash('success', 'Wali berhasil dihapus');
        return back();
    }
}