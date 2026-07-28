<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Siswa;

class AuthController extends Controller
{
    // ==================== ADMIN/GURU LOGIN ====================
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->where('is_aktif', true)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));

            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('guru.dashboard');
        }

        return back()->with('error', 'Email atau password salah.')->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    // ==================== SISWA LOGIN ====================
    public function showSiswaLogin()
    {
        return view('auth.siswa-login');
    }

    public function siswaLogin(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'no_hp' => 'required',
        ]);

        $siswa = Siswa::where('nis', $request->nis)
            ->where('no_hp', $request->no_hp)
            ->where('status', 'aktif')
            ->first();

        if ($siswa) {
            auth('siswa')->login($siswa, $request->boolean('remember'));
            return redirect()->route('siswa.dashboard');
        }

        return back()->with('error', 'NIS atau Nomor HP tidak ditemukan.')->withInput();
    }

    public function siswaLogout()
    {
        auth('siswa')->logout();
        return redirect()->route('siswa.login');
    }
}
