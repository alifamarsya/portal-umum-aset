@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-lg mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-[#114E84] transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('profile.show') }}" class="hover:text-[#114E84] transition-colors">Profil Saya</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">Ubah Password</span>
        </nav>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-[#114E84] to-[#1D6FB8] px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Ubah Password</h1>
                        <p class="text-blue-200 text-sm">Pastikan gunakan password yang kuat</p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="px-8 py-6">

                {{-- Error Alert --}}
                @if($errors->any())
                <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('profile.update-password') }}" id="form-ubah-password" class="space-y-5">
                    @csrf

                    {{-- Password Lama --}}
                    <div>
                        <label for="password_lama" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Password Lama <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_lama" id="password_lama" autocomplete="current-password"
                                   class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] transition-all placeholder-gray-400 @error('password_lama') border-red-400 bg-red-50 @enderror"
                                   placeholder="Masukkan password saat ini">
                            <button type="button" onclick="togglePassword('password_lama', 'eye_lama')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg id="eye_lama" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password_lama')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                   class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] transition-all placeholder-gray-400 @error('password') border-red-400 bg-red-50 @enderror"
                                   placeholder="Minimal 8 karakter">
                            <button type="button" onclick="togglePassword('password', 'eye_baru')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg id="eye_baru" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        {{-- Strength indicator --}}
                        <div id="strength-bar" class="mt-2 h-1.5 rounded-full bg-gray-200 overflow-hidden hidden">
                            <div id="strength-fill" class="h-full rounded-full transition-all duration-300 w-0"></div>
                        </div>
                        <p id="strength-label" class="mt-1 text-xs text-gray-400 hidden"></p>
                        @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                   class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] transition-all placeholder-gray-400"
                                   placeholder="Ulangi password baru">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye_konfirm')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg id="eye_konfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Tips Keamanan --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <p class="text-xs font-semibold text-[#114E84] mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tips keamanan password
                        </p>
                        <ul class="text-xs text-blue-700 space-y-1 list-none">
                            <li class="flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0"></span> Minimal 8 karakter</li>
                            <li class="flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0"></span> Kombinasi huruf besar, kecil, angka, dan simbol</li>
                            <li class="flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0"></span> Jangan gunakan informasi pribadi yang mudah ditebak</li>
                            <li class="flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0"></span> Jangan bagikan password kepada siapapun</li>
                        </ul>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" id="btn-submit"
                                class="flex-1 bg-gradient-to-r from-[#114E84] to-[#1D6FB8] text-white font-semibold py-2.5 rounded-xl hover:opacity-90 transition-all duration-200 shadow-sm hover:shadow-md text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Password
                        </button>
                        <a href="{{ route('profile.show') }}"
                           class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(eyeId);
    if (!field || !eye) return;
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    eye.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}

// Password strength meter
const pwdInput = document.getElementById('password');
const bar = document.getElementById('strength-bar');
const fill = document.getElementById('strength-fill');
const label = document.getElementById('strength-label');

const levels = [
    { min: 0,  max: 25, color: 'bg-red-500',    text: 'Lemah', textColor: 'text-red-600' },
    { min: 26, max: 50, color: 'bg-orange-400',  text: 'Cukup', textColor: 'text-orange-600' },
    { min: 51, max: 75, color: 'bg-yellow-400',  text: 'Baik',  textColor: 'text-yellow-600' },
    { min: 76, max: 100,color: 'bg-emerald-500', text: 'Kuat',  textColor: 'text-emerald-600' },
];

if (pwdInput) {
    pwdInput.addEventListener('input', function () {
        const val = this.value;
        if (!val) { bar.classList.add('hidden'); label.classList.add('hidden'); return; }
        bar.classList.remove('hidden');
        label.classList.remove('hidden');

        let score = 0;
        if (val.length >= 8) score += 25;
        if (/[A-Z]/.test(val)) score += 25;
        if (/[0-9]/.test(val)) score += 25;
        if (/[^A-Za-z0-9]/.test(val)) score += 25;

        const lvl = levels.find(l => score <= l.max) || levels[3];
        fill.className = `h-full rounded-full transition-all duration-300 ${lvl.color}`;
        fill.style.width = score + '%';
        label.className = `mt-1 text-xs font-medium ${lvl.textColor}`;
        label.textContent = 'Kekuatan: ' + lvl.text;
    });
}

// Loading state
const form = document.getElementById('form-ubah-password');
const btnSubmit = document.getElementById('btn-submit');
if (form && btnSubmit) {
    form.addEventListener('submit', function () {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menyimpan...`;
    });
}
</script>
@endsection
