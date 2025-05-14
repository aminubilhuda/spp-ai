<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User as Model;
use Illuminate\Support\Facades\Hash;

//refactor code
class UserController extends Controller
{
    private $viewIndex = 'user_index';
    private $viewCreate = 'user_form';
    private $viewEdit = 'user_form';
    private $viewShow = 'user_form';
    private $routePrefix = 'user';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view ('operator.'.$this->viewIndex, [
            'models' => Model::where('akses', 'operator')
            ->latest()
            ->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data User',
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
            'title' => 'Form Data User',
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
            'akses' => 'required|in:admin,operator',
        ]);
        // tambah user
        $requestData['password'] = Hash::make($requestData['password']);
        $requestData['email_verified_at'] = now();
        Model::create($requestData);
        $request->session()->flash('success', 'User berhasil ditambahkan');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = [
            'model' => Model::findOrFail($id),
            'title' => 'Detail User',
            'routePrefix' => $this->routePrefix
        ];
        return view('operator.user_detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // edit user
        $data = [
            'model' => Model::findOrFail($id),
            'method' => 'PUT',
            'action' => $this->routePrefix.'.update',
            'id' => $id,
            'title' => 'Edit User',
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
            'akses' => 'required|in:admin,operator',
        ]);       
        
        // Handle password
        if ($requestData['password'] == null) {
            unset($requestData['password']);
        } else {
            $requestData['password'] = Hash::make($requestData['password']);
        }
        
        // Update user
        Model::findOrFail($id)->update($requestData);
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // delete user
        $model =Model::findOrFail($id);

        if($model->id == 1 || $model->id == 2){
            session()->flash('error', 'User utama tidak bisa dihapus');
            return back();
        }

        $model->delete();

        session()->flash('success', 'User berhasil dihapus');
        return back();
    }
}