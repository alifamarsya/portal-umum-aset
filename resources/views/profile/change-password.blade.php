@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="space-y-6 w-full max-w-full">

    {{-- Breadcrumb & Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-[#114E84] transition">Dashboard</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('profile.show') }}" class="hover:text-[#114E84] transition">Profil Pengguna</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-600 font-medium">Ubah Password</span>
            </nav>
            <h1 class="text-2xl font-bold text-ink">Ubah Kata Sandi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui kata sandi akun Anda secara berkala untuk menjaga kerahasiaan dan keamanan sistem</p>
        </div>
        <div>
            <a href="{{ route('profile.show') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Profil</span>
            </a>
        </div>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-xs font-medium animate-enter">
            @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5'])
            <ul class="space-y-1 list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Change Password Card (Full Width) --}}
    <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">

        {{-- Header Banner --}}

        {{-- Form & Guidelines Grid --}}
        <div class="p-6 sm:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- Left: Form --}}
                <div class="lg:col-span-7">
                    <form method="POST" action="{{ route('profile.update-password') }}" id="form-ubah-password" class="space-y-5">
                        @csrf

                        {{-- Password Lama --}}
                        <div>
                            <label for="password_lama" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Password Lama <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_lama" id="password_lama" autocomplete="current-password" required
                                       class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/15 focus:border-[#114E84] transition placeholder-slate-400 @error('password_lama') border-rose-400 bg-rose-50/50 @enderror"
                                       placeholder="Masukkan kata sandi saat ini">
                                <button type="button" onclick="togglePassword('password_lama', 'eye_lama')"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                                        aria-label="Tampilkan password lama">
                                    <svg id="eye_lama" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password_lama')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Baru --}}
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Password Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" autocomplete="new-password" required minlength="8"
                                       class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/15 focus:border-[#114E84] transition placeholder-slate-400 @error('password') border-rose-400 bg-rose-50/50 @enderror"
                                       placeholder="Minimal 8 karakter unik">
                                <button type="button" onclick="togglePassword('password', 'eye_baru')"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                                        aria-label="Tampilkan password baru">
                                    <svg id="eye_baru" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Real-time strength meter --}}
                            <div id="strength-container" class="mt-2.5 hidden">
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div id="strength-fill" class="h-full rounded-full transition-all duration-300 w-0"></div>
                                </div>
                                <div class="flex items-center justify-between mt-1 text-[11px]">
                                    <span id="strength-label" class="font-semibold text-slate-500"></span>
                                    <span class="text-slate-400">Minimal 8 karakter</span>
                                </div>
                            </div>

                            @error('password')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Konfirmasi Password Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required
                                       class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#114E84]/15 focus:border-[#114E84] transition placeholder-slate-400"
                                       placeholder="Ulangi password baru yang sama">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye_konfirm')"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                                        aria-label="Tampilkan konfirmasi password">
                                    <svg id="eye_konfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <button type="submit" id="btn-submit"
                                    class="bg-[#114E84] hover:bg-[#0E4272] text-white font-semibold px-6 py-2.5 rounded-xl shadow-2xs transition text-sm flex items-center justify-center gap-2">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                                <span>Simpan Kata Sandi</span>
                            </button>
                            <a href="{{ route('profile.show') }}"
                               class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Right: Security guidelines card --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-blue-50/80 border border-blue-200/80 rounded-2xl p-5 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#114E84]">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84]'])
                            <span>Ketentuan Keamanan Password</span>
                        </div>
                        <ul class="text-xs text-slate-700 space-y-2 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84] mt-1.5 flex-shrink-0"></span>
                                <span>Panjang minimal <strong>8 karakter</strong>.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84] mt-1.5 flex-shrink-0"></span>
                                <span>Kombinasi huruf besar (A-Z) dan huruf kecil (a-z).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84] mt-1.5 flex-shrink-0"></span>
                                <span>Mengandung minimal satu angka numerik (0-9).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84] mt-1.5 flex-shrink-0"></span>
                                <span>Disarankan menyertakan simbol atau karakter khusus (!@#$%^&*).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84] mt-1.5 flex-shrink-0"></span>
                                <span>Hindari penggunaan tanggal lahir atau nama yang mudah ditebak.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs leading-relaxed flex items-start gap-3">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5'])
                        <p>Setelah kata sandi diperbarui, Anda akan tetap login pada sesi peramban ini dan perubahan akan dicatat pada audit log sistem.</p>
                    </div>
                </div>

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
const container = document.getElementById('strength-container');
const fill = document.getElementById('strength-fill');
const label = document.getElementById('strength-label');

const levels = [
    { min: 0,  max: 25, color: 'bg-rose-500',    text: 'Sangat Lemah', textColor: 'text-rose-600' },
    { min: 26, max: 50, color: 'bg-amber-400',   text: 'Cukup',        textColor: 'text-amber-600' },
    { min: 51, max: 75, color: 'bg-blue-500',    text: 'Baik',         textColor: 'text-blue-600' },
    { min: 76, max: 100,color: 'bg-emerald-500', text: 'Sangat Kuat',  textColor: 'text-emerald-600' },
];

if (pwdInput && container && fill && label) {
    pwdInput.addEventListener('input', function () {
        const val = this.value;
        if (!val) {
            container.classList.add('hidden');
            return;
        }
        container.classList.remove('hidden');

        let score = 0;
        if (val.length >= 8) score += 25;
        if (/[A-Z]/.test(val)) score += 25;
        if (/[0-9]/.test(val)) score += 25;
        if (/[^A-Za-z0-9]/.test(val)) score += 25;

        const lvl = levels.find(l => score <= l.max) || levels[3];
        fill.className = `h-full rounded-full transition-all duration-300 ${lvl.color}`;
        fill.style.width = Math.max(10, score) + '%';
        label.className = `font-semibold ${lvl.textColor}`;
        label.textContent = 'Kekuatan: ' + lvl.text;
    });
}

// Loading state on submit
const form = document.getElementById('form-ubah-password');
const btnSubmit = document.getElementById('btn-submit');
if (form && btnSubmit) {
    form.addEventListener('submit', function () {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>Menyimpan...</span>
        `;
    });
}
</script>
@endsection
