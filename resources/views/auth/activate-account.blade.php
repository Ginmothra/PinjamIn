<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-slate-800">Aktivasi Akun</h2>
        <p class="mt-2 text-sm text-slate-600">
            Selamat datang di PinjamIn! Silakan atur password Anda untuk mengaktifkan akun.
        </p>
    </div>

    <form method="POST" action="{{ route('activate-account.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Email (Read-only) -->
        <div class="mb-4">
            <x-input-label for="email_display" :value="__('Email')" />
            <x-text-input id="email_display" class="block mt-1 w-full bg-slate-50 text-slate-500 cursor-not-allowed" type="email" :value="$email" disabled />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center py-3">
                {{ __('Aktifkan Akun Sekarang') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
