{{-- ===================================================================
     PARTIAL: Notification Bell Dropdown
     Usage: @include('partials.notification-dropdown')
     Lonceng ASLI: jumlah belum dibaca + 8 notifikasi terbaru diambil dari
     tabel notifications (channel 'database'). Klik item menandai notifikasi
     dibaca lalu mengarahkan ke halaman tujuan (misalnya detail tiket).
     =================================================================== --}}
@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $latestNotifications = $user ? $user->notifications()->take(8)->get() : collect();
@endphp

<div class="relative" id="notifDropdownWrapper">
    {{-- Bell Button --}}
    <button id="notifBtn"
            type="button"
            aria-label="Notifikasi"
            class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:shadow-sm transition-all"
            onclick="toggleNotifDropdown()">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white" style="background:#1D6FB8;">{{ $unreadCount }} Baru</span>
                @endif
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold hover:underline" style="color:#1D6FB8;">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Body --}}
        <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-100">
            @forelse($latestNotifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $type = $data['type'] ?? '';
                    $title = $data['title'] ?? ($data['judul'] ?? 'Pemberitahuan');
                    $message = $data['message'] ?? ($data['pesan'] ?? '');
                    $actionUrl = route('notifications.read', $notification->id);
                @endphp

                <a href="{{ $actionUrl }}"
                   class="block px-4 py-3 transition {{ $isUnread ? ($type === 'sla_warning' ? 'bg-amber-50/60 hover:bg-amber-50' : 'bg-blue-50/50 hover:bg-blue-50/80') : 'hover:bg-slate-50' }}">
                    <div class="flex items-start gap-3">
                        {{-- Ikon menurut tipe notifikasi --}}
                        <div class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center
                            @if($type === 'ticket_rejected') bg-rose-100 text-rose-600
                            @elseif($type === 'ticket_completed') bg-amber-100 text-amber-700
                            @elseif($type === 'ticket_allocated' || $type === 'ticket_approved') bg-blue-100 text-[#1D6FB8]
                            @elseif($type === 'ticket_created') bg-sky-100 text-sky-700
                            @elseif($type === 'ticket_closed') bg-slate-200 text-slate-600
                            @elseif($type === 'sla_warning') bg-amber-100 text-amber-600 ring-1 ring-amber-300
                            @elseif($type === 'jatuh_tempo') bg-orange-100 text-orange-600
                            @else bg-slate-100 text-slate-500 @endif">
                            @if($type === 'ticket_rejected')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($type === 'ticket_completed' || $type === 'ticket_approved')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($type === 'ticket_allocated')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            @elseif($type === 'ticket_closed')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v12a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            @elseif($type === 'sla_warning')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/></svg>
                            @elseif($type === 'jatuh_tempo')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @endif
                        </div>

                        {{-- Isi notifikasi --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                <p class="text-xs font-bold text-slate-800 truncate {{ $isUnread ? ($type === 'sla_warning' ? 'text-amber-800' : 'text-[#1D6FB8]') : '' }}">
                                    {{ $title }}
                                </p>
                                @if($isUnread)
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $type === 'sla_warning' ? 'bg-amber-500' : 'bg-blue-600' }}"></span>
                                @endif
                            </div>
                            <p class="text-[11.5px] text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $message }}
                            </p>
                            @if($type === 'ticket_rejected' && !empty($data['reason']))
                                <p class="text-[11px] font-medium text-rose-600 mt-1 bg-rose-50 px-2 py-0.5 rounded border border-rose-200/60 line-clamp-1">
                                    Alasan: {{ $data['reason'] }}
                                </p>
                            @endif
                            <p class="text-[10px] text-slate-400 mt-1.5">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
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
            @endforelse
        </div>

        {{-- Footer --}}
        @if($latestNotifications->isNotEmpty())
            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold hover:underline" style="color:#1D6FB8;">
                    Lihat Semua Notifikasi &rarr;
                </a>
            </div>
        @endif
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
