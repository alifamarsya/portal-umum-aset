{{-- ===================================================================
     PARTIAL: Profile Dropdown
     Usage: @include('partials.profile-dropdown')
     =================================================================== --}}
@php
    $profileUser = auth()->user();
    $initials = collect(explode(' ', trim($profileUser?->nama_lengkap ?? 'U')))
        ->take(2)
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->implode('');
@endphp

<div class="relative" id="profileDropdownWrapper">
    {{-- Profile Button --}}
    <button id="profileBtn"
            type="button"
            aria-label="Profil Pengguna"
            class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-xl bg-white border border-slate-200 hover:border-slate-300 hover:shadow-sm transition-all group"
            onclick="toggleProfileDropdown()">
        {{-- Avatar Inisial --}}
        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
             style="background: linear-gradient(135deg, #1D6FB8 0%, #0F487F 100%);">
            {{ $initials }}
        </div>
        <div class="hidden sm:block text-left leading-tight">
            <p class="text-xs font-semibold text-slate-800 truncate max-w-[110px]">{{ $profileUser?->nama_lengkap }}</p>
            <p class="text-[10px] text-slate-500 truncate max-w-[110px]">{{ $profileUser?->role?->label }}</p>
        </div>
        {{-- Chevron --}}
        <svg id="profileChevron" class="w-3 h-3 text-slate-400 hidden sm:block transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown Panel --}}
    <div id="profileDropdown"
         class="hidden absolute right-0 top-full mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
         style="animation: dropIn 0.18s cubic-bezier(.22,1,.36,1);">

        {{-- User Info Header --}}
        <div class="px-4 py-3.5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background: linear-gradient(135deg, #1D6FB8 0%, #0F487F 100%);">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ $profileUser?->nama_lengkap }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ $profileUser?->username }}</p>
                    <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-semibold text-white"
                          style="background: #1D6FB8;">
                        {{ $profileUser?->role?->label }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <div class="py-1.5">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors group/item">
                <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover/item:bg-blue-50 flex items-center justify-center flex-shrink-0 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover/item:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span>Data Pribadi</span>
            </a>

            <a href="{{ route('profile.change-password') }}"
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors group/item">
                <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover/item:bg-blue-50 flex items-center justify-center flex-shrink-0 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover/item:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </span>
                <span>Ubah Password</span>
            </a>
        </div>

        {{-- Logout --}}
        <div class="px-3 pb-3 pt-1 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors group/item">
                    <span class="w-7 h-7 rounded-lg bg-red-50 group-hover/item:bg-red-100 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleProfileDropdown() {
        var dd = document.getElementById('profileDropdown');
        var chevron = document.getElementById('profileChevron');
        if (!dd) return;
        var isHidden = dd.classList.contains('hidden');
        closeAllDropdowns();
        if (isHidden) {
            dd.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    }
</script>
