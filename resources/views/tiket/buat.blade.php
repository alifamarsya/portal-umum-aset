@extends('layouts.app')
@section('title', 'Ajukan Tiket Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('tiket.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-brand transition mb-3">
            @include('partials.icon', ['name' => 'chevron-left', 'class' => 'w-3.5 h-3.5'])
            Kembali ke Daftar Tiket
        </a>
        <h1 class="text-xl font-extrabold text-ink">Ajukan Tiket Layanan</h1>
        <p class="text-sm text-slate-500 mt-1">Isi formulir di bawah untuk mengajukan permintaan layanan internal.</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('tiket.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-5">
        @csrf


        {{-- Kategori --}}
        <div>
            <label for="kategori_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                Kategori Layanan <span class="text-rose-500">*</span>
            </label>
            <select id="kategori_id" name="kategori_id" required
                    class="w-full text-sm rounded-xl border border-slate-200 px-4 py-2.5 focus:border-blue-400 outline-none bg-white transition @error('kategori_id') border-rose-400 @enderror">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('kategori_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nama }}
                        @if ($cat->department)
                            ({{ $cat->department->nama }})
                        @endif
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- Jenis Pengajuan & Tingkat Prioritas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Jenis Pengajuan --}}
            <div>
                <label for="jenis_pengajuan" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Jenis Pengajuan <span class="text-rose-500">*</span>
                </label>
                <select id="jenis_pengajuan" name="jenis_pengajuan" required
                        class="w-full text-sm rounded-xl border border-slate-200 px-4 py-2.5 focus:border-blue-400 outline-none bg-white transition @error('jenis_pengajuan') border-rose-400 @enderror">
                    <option value="">— Pilih Jenis Pengajuan —</option>
                    <option value="Permintaan" {{ old('jenis_pengajuan', 'Permintaan') == 'Permintaan' ? 'selected' : '' }}>
                        Permintaan
                    </option>
                    <option value="Permasalahan" {{ old('jenis_pengajuan') == 'Permasalahan' ? 'selected' : '' }}>
                        Permasalahan
                    </option>
                </select>
                <p class="mt-1 text-[11px] text-slate-400">Pilih permohonan baru atau kendala/masalah</p>
                @error('jenis_pengajuan') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            {{-- Tingkat Prioritas --}}
            <div>
                <label for="prioritas" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Tingkat Prioritas <span class="text-rose-500">*</span>
                </label>
                <select id="prioritas" name="prioritas" required
                        class="w-full text-sm rounded-xl border border-slate-200 px-4 py-2.5 focus:border-blue-400 outline-none bg-white transition @error('prioritas') border-rose-400 @enderror">
                    <option value="">— Pilih Tingkat Prioritas —</option>
                    <option value="Rendah" {{ old('prioritas') == 'Rendah' ? 'selected' : '' }}>
                        Rendah
                    </option>
                    <option value="Sedang" {{ old('prioritas', 'Sedang') == 'Sedang' ? 'selected' : '' }}>
                        Sedang
                    </option>
                    <option value="Tinggi" {{ old('prioritas') == 'Tinggi' ? 'selected' : '' }}>
                        Tinggi
                    </option>
                    <option value="Kritis" {{ old('prioritas') == 'Kritis' ? 'selected' : '' }}>
                        Kritis (Darurat)
                    </option>
                </select>
                <p class="mt-1 text-[11px] text-slate-400">Tingkat urgensi penanganan</p>
                @error('prioritas') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="deskripsi" class="block text-xs font-bold text-slate-700 mb-1.5">
                Deskripsi Detail <span class="text-rose-500">*</span>
            </label>
            <textarea id="deskripsi" name="deskripsi" rows="5" required
                      placeholder="Jelaskan secara rinci permintaan, kebutuhan, atau masalah yang Anda hadapi..."
                      class="w-full text-sm rounded-xl border border-slate-200 px-4 py-2.5 focus:border-blue-400 focus:ring-1 focus:ring-blue-300 outline-none transition resize-none @error('deskripsi') border-rose-400 @enderror">{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- Lampiran --}}
        <div>
            <label for="attachment" class="block text-xs font-bold text-slate-700 mb-1.5">Lampiran (Opsional)</label>
            <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                   class="w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition">
            <p class="mt-1 text-[11px] text-slate-400">Maks. 5 MB. Format: PDF, DOC, DOCX, JPG, PNG</p>
            @error('attachment') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- Info Box SLA --}}
        <div class="rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/70 p-4 space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-[#114E84]">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-blue-600'])
                <span>Ketentuan Service Level Agreement (SLA):</span>
            </div>
            <ul class="text-xs text-slate-600 space-y-1 pl-6 list-disc">
                <li><strong class="text-slate-800">SLA Response Time:</strong> Maksimal 2 jam kerja (08.00–17.00). Tiket masuk setelah 17.00 / akhir pekan, timer dimulai pukul 08.00 hari kerja berikutnya.</li>
                <li><strong class="text-slate-800">SLA Resolution Time:</strong> Timer pengerjaan otomatis aktif setelah tiket disetujui Kepala Bagian, dengan durasi disesuaikan berbasis kategori pekerjaan dan prioritas.</li>
            </ul>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 pt-1">
            <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-[#114E84] text-white text-sm font-bold hover:bg-[#0E4272] transition shadow-sm">
                Ajukan Tiket
            </button>
            <a href="{{ route('tiket.index') }}"
               class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection