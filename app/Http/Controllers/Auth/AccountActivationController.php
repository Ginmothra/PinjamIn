<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AccountActivationController extends Controller
{
    /**
     * Tampilkan form aktivasi akun.
     */
    public function show(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        return view('auth.activate-account', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    /**
     * Proses aktivasi akun.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Cek validitas token di database
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Token aktivasi tidak valid atau sudah kedaluwarsa.']);
        }

        $user = User::where('email', $request->email)->first();

        // Update password dan aktifkan user
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        // Hapus token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Akun Anda berhasil diaktifkan! Silakan login.');
    }
}
