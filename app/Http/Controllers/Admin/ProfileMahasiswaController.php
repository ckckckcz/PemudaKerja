<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Validator;

class ProfileMahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $colleges = Mahasiswa::limit(10)->get();
        return view('admin.manajemenProfile.mahasiswa', compact('colleges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodis = ProgramStudi::all();
        return view('admin.function.profile_mahasiswa.tambah', compact('prodis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|string|max:20|unique:mahasiswa',
            'nama_mahasiswa' => 'required|string|max:255',
            'id_program_studi' => 'required|exists:program_studi,id_program_studi',
            'jurusan' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
        ], [
            'nim.required' => 'NIM Harus Diisi',
            'nama_mahasiswa.required' => 'Nama Mahasiswa Harus Diisi',
            'id_program_studi.required' => 'Nama Program Studi Harus Diisi',
            'jurusan.required' => 'Jurusan Harus Diisi',
            'jenis_kelamin.required' => 'Jenis kelamin Harus Diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::transaction(function () use ($request) {
            $user = User::create([
                'username' => $request->nim,
                'email' => $request->nim . '@example.com',
                'password' => bcrypt($request->nim),
                'role' => 'mahasiswa',
            ]);

            Mahasiswa::create([
                'nim' => $request->nim,
                'nama_mahasiswa' => $request->nama_mahasiswa,
                'id_program_studi' => $request->id_program_studi,
                'jurusan' => $request->jurusan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'id_user' => $user->id_user,
            ]);
        });


        return redirect()->route('admin.profile.mahasiswa')->with('success', 'Data Mahasiswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $prodis = ProgramStudi::all();
        $mahasiswa = Mahasiswa::findOrFail($id);
        return view('admin.function.profile_mahasiswa.edit', compact('prodis', 'mahasiswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|string|max:20',
            'nama_mahasiswa' => 'required|string|max:255',
            'id_program_studi' => 'required|exists:program_studi,id_program_studi',
            'jurusan' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
        ], [
            'nim.required' => 'NIM Harus Diisi',
            'nama_mahasiswa.required' => 'Nama Mahasiswa Harus Diisi',
            'id_program_studi.required' => 'Nama Program Studi Harus Diisi',
            'jurusan.required' => 'Jurusan Harus Diisi',
            'jenis_kelamin.required' => 'Jenis kelamin Harus Diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        Mahasiswa::find($id)->update([
            'nim' => $request->nim,
            'nama_mahasiswa' => $request->nama_mahasiswa,
            'id_program_studi' => $request->id_program_studi,
            'jurusan' => $request->jurusan,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return redirect()->route('admin.profile.mahasiswa')->with('success', 'Data Mahasiswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $user = User::where('id_user', $mahasiswa->id_user)->first();

        $mahasiswa->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.profile.mahasiswa')->with('success', 'Data Mahasiswa berhasil dihapus.');
    }
}
