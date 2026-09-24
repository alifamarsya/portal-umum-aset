{{-- ===================================================================
     PARTIAL: Notification Bell Dropdown
     Usage: @include('partials.notification-dropdown')
     Note: Backend notification system belum di-setup — UI placeholder.
     =================================================================== --}}
@php
    // Placeholder: 0 notifikasi belum dibaca
    // Ganti dengan query nyata jika system notifikasi sudah ada:
    // $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
    $unreadCount = 0;
@endphp

<div class="relative" id="notifDropdownWrapper">
    {{-- Bell Button --}}
    <button id="notifBtn"
            type="button"
            aria-label="Notifikasi"
            class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:shadow-sm transition-all"
            onclick="toggleNotifDropdown()">
        <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 min-w-[17px] h-[17px] bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none shadow-sm">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @else
            {{-- Dot indicator (subtle, untuk tanda bell aktif) --}}
            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full border-2 border-white"
                  style="background-color: #1D6FB8; opacity: 0.7;"></span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div id="notifDropdown"
         class="hidden absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
         style="animation: dropIn 0.18s cubic-bezier(.22,1,.36,1);">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="text-sm font-semibold text-slate-800">Notifikasi</span>
                @if($unreadCount > 0)
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white" style="background:#1D6FB8;">{{ $unreadCount }}</span>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div class="max-h-[320px] overflow-y-auto">
            {{-- Empty State --}}
            <div class="py-10 flex flex-col items-center justify-center text-center px-4">
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-600">Belum ada notifikasi</p>
                <p class="text-xs text-slate-400 mt-1">Notifikasi baru akan muncul di sini</p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes dropIn {
        from { opacity: 0; transform: translateY(-6px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>
<script>
    function toggleNotifDropdown() {
        var dd = document.getElementById('notifDropdown');
        if (!dd) return;
        var isHidden = dd.classList.contains('hidden');
        // Tutup dropdown lain dulu
        closeAllDropdowns();
        if (isHidden) {
            dd.classList.remove('hidden');
        }
    }
</script>
