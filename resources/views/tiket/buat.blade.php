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
    <p class="text-slate-500 text-xs mt-1">Sampaikan kendala teknis atau kebutuhan fasilitas operasional Anda secara jelas dan terperinci.</p>
</div>

<form method="POST" action="{{ route('tiket.store') }}" enctype="multipart/form-data" id="ticket-create-form"
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
        <div class="flex items-center justify-between mb-1.5">
            <label for="deskripsi" class="block text-sm font-semibold text-slate-700">
                Deskripsi / Uraian Masalah <span class="text-rose-500">*</span>
            </label>
            <span id="char-counter-badge" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 transition">
                <span id="char-count">0</span> / 100 karakter minimal
            </span>
        </div>
        <textarea name="deskripsi" id="deskripsi" rows="6" required minlength="100"
                  placeholder="Jelaskan kebutuhan, lokasi ruangan, kendala teknis, atau rincian permohonan secara jelas dan lengkap (wajib minimal 100 karakter)..."
                  class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none transition resize-none @error('deskripsi') border-rose-400 @enderror">{{ old('deskripsi') }}</textarea>

        <div id="deskripsi-error" class="hidden mt-1 text-xs text-rose-500 font-medium"></div>

        @error('deskripsi')
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Lampiran Dokumen / Foto Pendukung (Multi-File) --}}
    <div>
        <label for="attachments" class="block text-sm font-semibold text-slate-700 mb-1">
            Lampiran Dokumen / Foto Pendukung
            <span class="text-xs text-slate-400 font-normal">(Opsional, bisa lebih dari 1 file, maks. 10 MB per berkas)</span>
        </label>
        <p class="text-[12px] text-slate-500 mb-2">
            Format yang didukung: <strong>PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</strong>.
        </p>

        {{-- Drop / Select area --}}
        <div id="file-dropzone" class="relative border-2 border-dashed border-slate-300 hover:border-[#114E84] rounded-2xl p-5 text-center transition bg-slate-50/50">
            <input type="file" name="attachments[]" id="attachments" multiple
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <div class="flex flex-col items-center pointer-events-none">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-[#114E84] flex items-center justify-center mb-2">
                    @include('partials.icon', ['name' => 'paperclip', 'class' => 'w-5 h-5'])
                </div>
                <p class="text-xs font-semibold text-slate-700">
                    Klik untuk memilih berkas atau seret berkas ke sini
                </p>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Dapat memilih beberapa file sekaligus (maksimal 10 MB/file)
                </p>
            </div>
        </div>

        {{-- Preview list of selected files --}}
        <div id="file-preview-container" class="hidden mt-3 space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-700">
                    Berkas yang dipilih: <span id="file-count-badge" class="text-[#114E84] font-bold">(0)</span>
                </p>
                <button type="button" id="btn-clear-all" class="text-[11px] text-slate-400 hover:text-rose-600 transition">
                    Hapus Semua
                </button>
            </div>
            <div id="file-preview-list" class="space-y-1.5"></div>
        </div>

        @error('attachments')
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        @error('attachments.*')
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
            <li><strong class="text-slate-800">SLA Response Time:</strong> Maksimal 2 jam kerja (08.00–17.00 WITA, Senin–Jumat). Tiket yang masuk di luar jam kerja mulai dihitung pukul 08.00 WITA hari kerja berikutnya.</li>
            <li><strong class="text-slate-800">SLA Resolution Time:</strong> Timer pengerjaan resmi aktif setelah disetujui Kepala Bagian terkait sesuai prioritas dan jenis pekerjaan.</li>
        </ul>
    </div>

    {{-- Submit Buttons --}}
    <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
        <button type="submit" id="btn-submit"
                class="bg-[#114E84] hover:bg-[#0E4272] disabled:opacity-50 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow transition flex items-center gap-2">
            <span>Ajukan Tiket</span>
        </button>
        <a href="{{ route('tiket.index') }}"
           class="text-sm text-slate-500 hover:text-slate-800 px-4 py-2.5 font-medium transition">
            Batal
        </a>
    </div>
</form>

{{-- Client-side validation & character counter JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('deskripsi');
    const counterText = document.getElementById('char-count');
    const badge = document.getElementById('char-counter-badge');
    const errorEl = document.getElementById('deskripsi-error');
    const form = document.getElementById('ticket-create-form');
    const fileInput = document.getElementById('attachments');
    const previewContainer = document.getElementById('file-preview-container');
    const previewList = document.getElementById('file-preview-list');
    const fileCountBadge = document.getElementById('file-count-badge');
    const clearAllBtn = document.getElementById('btn-clear-all');
    const minLength = 100;
    const maxFileSize = 10 * 1024 * 1024; // 10MB

    // Character counter logic without green progress bar
    function updateCounter() {
        const length = textarea.value.trim().length;
        counterText.textContent = length;

        if (length >= minLength) {
            badge.className = 'text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300 transition';
            errorEl.classList.add('hidden');
            textarea.classList.remove('border-rose-400');
        } else if (length > 0) {
            badge.className = 'text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-300 transition';
        } else {
            badge.className = 'text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 transition';
        }
    }

    textarea.addEventListener('input', updateCounter);
    updateCounter();

    // DataTransfer to accumulate files properly across multiple selections
    let dt = new DataTransfer();

    function formatBytes(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        }
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function renderFileList() {
        previewList.innerHTML = '';
        const files = dt.files;

        if (files.length > 0) {
            previewContainer.classList.remove('hidden');
            if (fileCountBadge) fileCountBadge.textContent = `(${files.length} berkas)`;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const isTooLarge = file.size > maxFileSize;
                const ext = file.name.split('.').pop().toLowerCase();

                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-2.5 rounded-xl border ' + 
                    (isTooLarge ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-slate-50 border-slate-200 text-slate-700') + 
                    ' text-xs transition hover:bg-slate-100/80';
                
                item.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-3">
                        <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-white border border-slate-200 uppercase font-semibold text-slate-700 flex-shrink-0">
                            ${ext}
                        </span>
                        <span class="truncate font-medium text-slate-800" title="${file.name}">${file.name}</span>
                        <span class="text-slate-400 whitespace-nowrap flex-shrink-0">(${formatBytes(file.size)})</span>
                    </div>
                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        ${isTooLarge 
                            ? '<span class="text-rose-600 font-bold">⚠️ Melebihi 10 MB</span>' 
                            : '<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200 text-[11px]">✓ Siap upload</span>'
                        }
                        <button type="button" onclick="removeAttachment(${i})" 
                                class="w-6 h-6 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-100/60 flex items-center justify-center transition"
                                title="Hapus berkas ini" aria-label="Hapus berkas ${file.name}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `;
                previewList.appendChild(item);
            }
        } else {
            previewContainer.classList.add('hidden');
            if (fileCountBadge) fileCountBadge.textContent = '(0)';
        }
    }

    function addFiles(fileArray) {
        for (let i = 0; i < fileArray.length; i++) {
            const newFile = fileArray[i];
            let isDuplicate = false;
            for (let j = 0; j < dt.items.length; j++) {
                const existing = dt.files[j];
                if (existing.name === newFile.name && existing.size === newFile.size && existing.lastModified === newFile.lastModified) {
                    isDuplicate = true;
                    break;
                }
            }
            if (!isDuplicate) {
                dt.items.add(newFile);
            }
        }
        fileInput.files = dt.files;
        renderFileList();
    }

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            addFiles(Array.from(this.files));
        }
    });

    // Drag and Drop support
    const dropzone = document.getElementById('file-dropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-[#114E84]', 'bg-blue-50/60');
            }, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-[#114E84]', 'bg-blue-50/60');
            }, false);
        });
        dropzone.addEventListener('drop', function(e) {
            const dtDrop = e.dataTransfer;
            if (dtDrop && dtDrop.files && dtDrop.files.length > 0) {
                addFiles(Array.from(dtDrop.files));
            }
        }, false);
    }

    window.removeAttachment = function (index) {
        const nextDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) {
                nextDt.items.add(dt.files[i]);
            }
        }
        dt = nextDt;
        fileInput.files = dt.files;
        renderFileList();
    };

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            dt = new DataTransfer();
            fileInput.files = dt.files;
            renderFileList();
        });
    }

    form.addEventListener('submit', function (e) {
        const length = textarea.value.trim().length;
        if (length < minLength) {
            e.preventDefault();
            errorEl.textContent = `Deskripsi / uraian masalah wajib diisi minimal 100 karakter. Saat ini baru ${length} karakter (kurang ${minLength - length} karakter lagi).`;
            errorEl.classList.remove('hidden');
            textarea.classList.add('border-rose-400');
            textarea.focus();
            return false;
        }

        // Sync files before submit
        fileInput.files = dt.files;

        // Check file sizes
        if (fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                if (fileInput.files[i].size > maxFileSize) {
                    e.preventDefault();
                    alert(`Berkas "${fileInput.files[i].name}" melebihi batas maksimal 10 MB. Silakan hapus berkas tersebut.`);
                    return false;
                }
            }
        }
    });
});
</script>
@endsection