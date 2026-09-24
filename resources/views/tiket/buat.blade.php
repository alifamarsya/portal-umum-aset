@extends('layouts.app')
@section('title', 'Buat Tiket Layanan Baru')

@section('content')
{{-- Header Area --}}
<div class="mb-6">
    <a href="{{ route('tiket.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#114E84] transition mb-2">
        &larr; Kembali ke Daftar Tiket
    </a>
    <p class="text-[12px] font-bold uppercase tracking-wider text-amber-600 mb-0.5">Formulir Permintaan</p>
    <h1 class="text-2xl font-bold text-ink">Buat Tiket Layanan Baru</h1>
    <p class="text-slate-500 text-xs mt-1">Sampaikan kendala atau kebutuhan operasional Anda. Tiket akan diverifikasi oleh Operator Helpdesk.</p>
</div>

<form method="POST" action="{{ route('tiket.store') }}" enctype="multipart/form-data"
      class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7 space-y-6">
    @csrf

    {{-- Pemohon Info Preview --}}
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row justify-between gap-3 text-xs">
        <div>
            <span class="text-slate-400 block font-medium">Nama Pemohon:</span>
            <span class="font-bold text-ink text-sm">{{ auth()->user()->nama_lengkap }}</span>
        </div>
        <div>
            <span class="text-slate-400 block font-medium">Bagian / Divisi:</span>
            <span class="font-semibold text-slate-700">{{ auth()->user()->bagian ?? '-' }}</span>
        </div>
        <div>
            <span class="text-slate-400 block font-medium">Jabatan:</span>
            <span class="font-semibold text-slate-700">{{ auth()->user()->jabatan ?? '-' }}</span>
        </div>
    </div>

    {{-- Kategori Layanan --}}
    <div>
        <label for="kategori_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
            Kategori Layanan <span class="text-rose-500">*</span>
        </label>
        <select name="kategori_id" id="kategori_id" required
                class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none bg-white transition @error('kategori_id') border-rose-400 @enderror">
            <option value="" disabled {{ old('kategori_id') ? '' : 'selected' }}>— Pilih Kategori Layanan —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('kategori_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nama }}
                    @if ($cat->department)
                        ({{ $cat->department->nama }})
                    @endif
                </option>
            @endforeach
        </select>
        @error('kategori_id')
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Jenis Pengajuan & Tingkat Prioritas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Jenis Pengajuan --}}
        <div>
            <label for="jenis_pengajuan" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Jenis Pengajuan <span class="text-rose-500">*</span>
            </label>
            <select name="jenis_pengajuan" id="jenis_pengajuan" required
                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none bg-white transition @error('jenis_pengajuan') border-rose-400 @enderror">
                <option value="" disabled {{ old('jenis_pengajuan') ? '' : 'selected' }}>— Pilih Jenis Pengajuan —</option>
                <option value="Permintaan" {{ old('jenis_pengajuan') === 'Permintaan' ? 'selected' : '' }}>
                    Permintaan (Pengajuan Baru / Fasilitas)
                </option>
                <option value="Permasalahan" {{ old('jenis_pengajuan') === 'Permasalahan' ? 'selected' : '' }}>
                    Permasalahan (Kendala Teknis / Kerusakan)
                </option>
            </select>
            @error('jenis_pengajuan')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tingkat Prioritas --}}
        <div>
            <label for="prioritas" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Tingkat Prioritas <span class="text-rose-500">*</span>
            </label>
            <select name="prioritas" id="prioritas" required
                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none bg-white transition @error('prioritas') border-rose-400 @enderror">
                <option value="" disabled {{ old('prioritas') ? '' : 'selected' }}>— Pilih Tingkat Prioritas —</option>
                <option value="Rendah" {{ old('prioritas') === 'Rendah' ? 'selected' : '' }}>Rendah (SLA 72 Jam)</option>
                <option value="Sedang" {{ old('prioritas') === 'Sedang' ? 'selected' : '' }}>Sedang (SLA 48 Jam)</option>
                <option value="Tinggi" {{ old('prioritas') === 'Tinggi' ? 'selected' : '' }}>Tinggi (SLA 12 Jam)</option>
                <option value="Kritis" {{ old('prioritas') === 'Kritis' ? 'selected' : '' }}>Kritis (SLA 4 Jam)</option>
            </select>
            @error('prioritas')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Deskripsi / Uraian Masalah --}}
    <div>
        <label for="deskripsi" class="block text-sm font-semibold text-slate-700 mb-1.5">
            Deskripsi / Uraian Masalah <span class="text-rose-500">*</span>
        </label>
        <textarea name="deskripsi" id="deskripsi" rows="5" required
                  placeholder="Jelaskan kebutuhan, lokasi, kendala teknis, atau permintaan layanan secara rinci..."
                  class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none transition resize-none @error('deskripsi') border-rose-400 @enderror">{{ old('deskripsi') }}</textarea>
        @error('deskripsi')
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Lampiran Dokumen / Foto --}}
    <div>
        <label for="attachment" class="block text-sm font-semibold text-slate-700 mb-1.5">
            Lampiran Dokumen / Foto Pendukung <span class="text-xs text-slate-400 font-normal">(Opsional, maks. 5 MB: PDF, DOC, DOCX, JPG, PNG)</span>
        </label>
        <input type="file" name="attachment" id="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-xl p-1.5">
        @error('attachment')
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Info Box SLA --}}
    <div class="rounded-xl bg-gradient-to-r from-blue-50/80 to-indigo-50/50 border border-blue-200/80 p-4 space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold text-[#114E84]">
            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-[#114E84]'])
            <span>Ketentuan Service Level Agreement (SLA):</span>
        </div>
        <ul class="text-xs text-slate-600 space-y-1 pl-5 list-disc leading-relaxed">
            <li><strong class="text-slate-800">SLA Response Time:</strong> Maksimal 2 jam kerja (08.00–17.00 WIB, Senin–Jumat). Tiket yang masuk di luar jam kerja mulai dihitung pukul 08.00 WIB hari kerja berikutnya.</li>
            <li><strong class="text-slate-800">SLA Resolution Time:</strong> Timer pengerjaan resmi aktif setelah disetujui Kepala Bagian terkait sesuai prioritas dan kategori pekerjaan.</li>
        </ul>
    </div>

    {{-- Submit Buttons --}}
    <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
        <button type="submit"
                class="bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow transition">
            Ajukan Tiket
        </button>
        <a href="{{ route('tiket.index') }}"
           class="text-sm text-slate-500 hover:text-slate-800 px-4 py-2.5 font-medium transition">
            Batal
        </a>
    </div>
</form>
@endsection