{{-- Halaman daftar notifikasi user.
     Diadaptasi dari resources/views/notifications/index.blade.php portum:
     warna brand #114E84 diganti #1D6FB8 (warna portal), dan ikon
     @include('partials.icon') diganti SVG inline karena portal tidak punya
     partial icon. Struktur dan perilakunya sama. --}}
@extends('layouts.app')
@section('title', 'Notifikasi Saya')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                    <svg class="w-7 h-7 text-[#1D6FB8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifikasi Saya
                </h1>
                <p class="text-xs text-slate-500 mt-1">Riwayat pemberitahuan status tiket dan informasi permohonan layanan</p>
            </div>

            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-4 py-2 rounded-xl transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Daftar Notifikasi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $type = $data['type'] ?? '';
                        $title = $data['title'] ?? ($data['judul'] ?? 'Pemberitahuan');
                        $message = $data['message'] ?? ($data['pesan'] ?? '');
                        $actionUrl = route('notifications.read', $notification->id);
                    @endphp

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-blue-50/30' : 'hover:bg-slate-50' }} transition">
                        <div class="flex items-start gap-3.5">
                            {{-- Ikon menurut tipe notifikasi --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @if($type === 'ticket_rejected')
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @elseif($type === 'ticket_completed')
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @elseif($type === 'ticket_closed')
                                    <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v12a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                @elseif($type === 'ticket_allocated' || $type === 'ticket_approved')
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#1D6FB8] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @elseif($type === 'ticket_created')
                                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                @elseif($type === 'sla_warning')
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center ring-1 ring-amber-300">
                                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/></svg>
                                    </div>
                                @elseif($type === 'jatuh_tempo')
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Isi notifikasi --}}
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-bold text-ink {{ $isUnread ? ($type === 'sla_warning' ? 'text-amber-800' : 'text-[#1D6FB8]') : '' }}">
                                        {{ $title }}
                                    </h3>
                                    @if($isUnread)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $type === 'sla_warning' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">Baru</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed mb-2">
                                    {{ $message }}
                                </p>
                                @if($type === 'ticket_rejected' && !empty($data['reason']))
                                    <div class="p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium mb-2">
                                        <strong>Alasan Penolakan:</strong> {{ $data['reason'] }}
                                    </div>
                                @endif
                                <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $notification->created_at->translatedFormat('d M Y, H:i') }} ({{ $notification->created_at->diffForHumans() }})</span>
                                </p>
                            </div>
                        </div>

                        {{-- Tombol aksi --}}
                        <div class="flex-shrink-0 flex items-center gap-2 self-end sm:self-center">
                            <a href="{{ $actionUrl }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-sm transition"
                               style="background:#1D6FB8;"
                               onmouseover="this.style.background='#155A94'"
                               onmouseout="this.style.background='#1D6FB8'">
                                <span>Lihat Detail</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 px-4 text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Belum Ada Notifikasi</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            Semua notifikasi dan informasi pembaruan tiket layanan Anda akan tersimpan dan tampil di sini.
                        </p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
