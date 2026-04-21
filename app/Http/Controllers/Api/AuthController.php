<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user dan kembalikan Sanctum token.
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Validasi role: Hanya role 'user' yang boleh masuk ke aplikasi mobile
        if (! $user->hasRole('user')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Akses ditolak. Hanya akun dengan role User yang dapat masuk ke aplikasi mobile.'],
            ]);
        }

        // Hapus token lama jika ada (opsional — bisa dihapus untuk multi-device)
        $user->tokens()->where('name', 'mobile')->delete();

        // Buat token Sanctum
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token'   => $token,
        ]);
    }

    /**
     * Logout — revoke token saat ini.
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Ambil data user yang sedang login.
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $unpaidSum = $user->fines()->where('status', 'unpaid')->sum('amount');

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'unpaid_fines_sum' => (float) $unpaidSum,
            ],
        ]);
    }

    /**
     * Kirim link reset password ke email.
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Link reset password telah dikirimkan ke email Anda. Silakan cek kotak masuk Anda.',
        ]);
    }

    /**
     * Reset password menggunakan token dari email.
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login dengan password baru Anda.',
        ]);
    }

    /**
     * Aktivasi Akun (Flow User Baru)
     */
    public function activateAccount(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // Cek apakah token valid di database
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return response()->json(['message' => 'Token aktivasi tidak valid atau sudah kedaluwarsa.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Update password dan aktifkan user
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        // Hapus token setelah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Akun berhasil diaktifkan! Silakan login menggunakan password baru Anda.'
        ]);
    }
}
