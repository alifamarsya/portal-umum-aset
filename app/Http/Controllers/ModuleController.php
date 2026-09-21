<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\AmortisasiCalculator;

// Mesin CRUD generik untuk 20 modul transaksional Portum
class ModuleController extends Controller
{
    use LogsAudit;

    public function __construct(private AmortisasiCalculator $amortisasiCalculator)
    {
    }

    private function config(string $key): array
    {
        $cfg = config("modules.$key");
        abort_if(!$cfg, 404, "Modul '$key' tidak ditemukan.");
        return $cfg;
    }

    private function authorizeModule(string $key, string $mode = 'read'): array
    {
        $cfg = $this->config($key);
        $user = auth()->user();

        // 1. Mode Checker (Approve / Reject)
        if ($mode === 'checker') {
            abort_unless($user && $user->isChecker(), 403, 'Hanya Pimpinan Divisi (Checker) yang dapat menyetujui atau menolak transaksi.');
            return $cfg;
        }

        // 2. Mode Read (Melihat data modul)
        if ($mode === 'read') {
            abort_unless($user && $user->canAccessModule($cfg['perm']), 403, "Role Anda tidak memiliki akses ke modul {$cfg['modul']}.");
            return $cfg;
        }

        // 3. Mode Write (Maker: Tambah, Ubah, Hapus data)
        abort_unless($user && $user->canAccessModule($cfg['perm']), 403, "Role Anda tidak memiliki akses ke modul {$cfg['modul']}.");
        abort_unless($user && $user->canWriteModule($cfg['perm']), 403, "Role Anda hanya bisa melihat modul {$cfg['modul']}, tidak bisa menambah atau mengubah data.");

        return $cfg;
    }

    public function index(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'read');
        $model = $cfg['model'];

        $items = $model::query()
            ->when($request->q, function ($q) use ($cfg, $request) {
                $q->where(function ($qq) use ($cfg, $request) {
                    foreach (array_keys($cfg['fields']) as $field) {
                        $qq->orWhere($field, 'like', '%' . $request->q . '%');
                    }
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return $this->resolveView('index', $key, compact('cfg', 'items', 'key'));
    }

    public function create(string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        return $this->resolveView('form', $key, ['cfg' => $cfg, 'key' => $key, 'item' => null]);
    }

    public function store(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);

        // ===== PERBAIKAN UTAMA (semua modul) =====
        // Hapus field yang nilainya null supaya default database dipakai
        // Mencegah error: Column 'xxx' cannot be null
        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });
        // ========================================

        if ($cfg['maker_checker']) {
            $data['maker_id'] = auth()->id();
            $data['approval_status'] = 'Diajukan';
        }

        if (array_key_exists('dibuat_oleh', $cfg['fields'])) {
            $data['dibuat_oleh'] = auth()->user()->nama_lengkap ?? auth()->user()->name;
        }

        // Fallback status jika masih kosong
        if (empty($data['status'])) {
            $data['status'] = 'Draft';
        }

        $item = $cfg['model']::create($data);
        $this->audit('CREATE', $cfg['modul'], $cfg['judul'], $item->id, 'Menambahkan data baru');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil ditambahkan.");
    }

    public function edit(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        return $this->resolveView('form', $key, ['cfg' => $cfg, 'key' => $key, 'item' => $item]);
    }

    public function update(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);

        // Hapus null supaya tidak menimpa data lama dengan null
        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });

        // Fallback status
        if (array_key_exists('status', $data) && empty($data['status'])) {
            $data['status'] = $item->status ?? 'Draft';
        }

        $item->update($data);
        $this->audit('UPDATE', $cfg['modul'], $cfg['judul'], $item->id, 'Mengubah data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil diperbarui.");
    }

    public function destroy(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        $item->delete();
        $this->audit('DELETE', $cfg['modul'], $cfg['judul'], $id, 'Menghapus data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil dihapus.");
    }

    // --- Alur Maker-Checker ---

    public function approve(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'checker');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('approve', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Disetujui',
            'approved_at' => now(),
        ]);
        $this->audit('APPROVE', $cfg['modul'], $cfg['judul'], $item->id, 'Menyetujui transaksi (checker)');

        return back()->with('status', 'Disetujui.');
    }

    public function reject(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'checker');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('reject', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Ditolak',
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan'),
        ]);
        $this->audit('REJECT', $cfg['modul'], $cfg['judul'], $item->id, 'Menolak transaksi (checker): ' . $request->input('catatan'));

        return back()->with('status', 'Ditolak.');
    }

    private function validated(Request $request, array $cfg): array
    {
        // 1. Ambil semua input
        $input = $request->all();

        // 2. Ubah string kosong menjadi null
        foreach ($input as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $input[$key] = null;
            }
        }

        // 3. Khusus field money & number yang kosong → set ke 0
        foreach ($cfg['fields'] as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            if (in_array($type, ['money', 'number']) && array_key_exists($field, $input) && $input[$field] === null) {
                $input[$field] = 0;
            }
        }

        $request->merge($input);

        // 4. Bangun rules validasi
        $rules = [];
        foreach ($cfg['fields'] as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            $isRequired = $meta['req'] ?? false;

            $rule = $isRequired ? 'required' : 'nullable';

            $rule .= match ($type) {
                'date'            => '|date',
                'number', 'money' => '|numeric|min:0',
                'checkbox'        => '|boolean',
                'file'            => '|string|max:500',
                'select'          => isset($meta['opts'])
                                        ? '|string|in:' . implode(',', $meta['opts'])
                                        : '|string|max:2000',
                default           => '|string|max:2000',
            };

            $rules[$field] = $rule;
        }

        return $request->validate($rules);
    }

    private function hitungAmortisasiJikaPerlu(string $key, array $data): array
    {
        if ($key !== 'amortisasi') {
            return $data;
        }

        if (empty($data['nilai_per_bulan']) && !empty($data['nilai_perolehan']) && !empty($data['umur_bulan'])) {
            $data['nilai_per_bulan'] = $this->amortisasiCalculator->hitungNilaiPerBulan(
                (float) $data['nilai_perolehan'],
                (int) $data['umur_bulan']
            );
        }

        if (!empty($data['tanggal_mulai']) && !empty($data['nilai_per_bulan'])) {
            $bulanBerjalan = $this->amortisasiCalculator->hitungBulanBerjalan(new \DateTime($data['tanggal_mulai']));
            $data['akumulasi'] = $this->amortisasiCalculator->hitungAkumulasi((float) $data['nilai_per_bulan'], $bulanBerjalan);
            $data['nilai_buku'] = $this->amortisasiCalculator->hitungNilaiBuku((float) $data['nilai_perolehan'], $data['akumulasi']);
        }

        return $data;
    }

    private function resolveView(string $type, string $key, array $data = []): \Illuminate\Contracts\View\View
    {
        $role = auth()->user()?->role?->nama;
        $folder = match ($role) {
            'superadmin' => 'admin',
            'pimpinan'   => 'pimpinan',
            'umum_rt'    => 'umum',
            'aset'       => 'aset',
            'pengadaan'  => 'pengadaan',
            default      => null,
        };

        // 1. Cek view spesifik modul di folder role
        if ($folder && view()->exists("{$folder}.{$key}.{$type}")) {
            return view("{$folder}.{$key}.{$type}", $data);
        }

        // 2. Cek view generik di folder role
        if ($folder && view()->exists("{$folder}.{$type}")) {
            return view("{$folder}.{$type}", $data);
        }

        // 3. Fallback generic
        return view("modules.{$type}", $data);
    }
}