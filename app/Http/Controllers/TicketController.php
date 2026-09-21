<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    use LogsAudit;

    public function __construct(private TicketService $ticketService) {}

    // ── Daftar Tiket (filtered by role) ──────────────────────────────────────

    public function index(Request $request)
    {
        $user  = auth()->user();
        abort_unless($user && $user->canAccessModule('tiket'), 403, 'Role Anda tidak memiliki akses ke Sistem Tiket.');

        $query = Ticket::visibleBy($user)
            ->with(['pemohon', 'kategori', 'department', 'assignedStaf'])
            ->latest();

        // Filter status (opsional)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter SLA status (opsional)
        if ($request->filled('sla_status')) {
            if ($request->sla_status === 'overdue') {
                $query->where(function ($q) {
                    $q->where('sla_status', 'overdue')
                      ->orWhere('response_sla_status', 'breached')
                      ->orWhere('resolution_sla_status', 'breached')
                      ->orWhere(function ($sub) {
                          $sub->whereNull('responded_at')
                              ->whereNotNull('response_due_at')
                              ->where('response_due_at', '<', now());
                      })
                      ->orWhere(function ($sub) {
                          $sub->whereNull('resolved_at')
                              ->whereNotNull('resolution_due_at')
                              ->where('resolution_due_at', '<', now());
                      });
                });
            } else {
                $query->where('sla_status', $request->sla_status);
            }
        }

        // Search nomor/judul
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_tiket', 'like', "%{$q}%")
                    ->orWhere('judul', 'like', "%{$q}%");
            });
        }

        $tikets    = $query->paginate(15)->withQueryString();
        $statuses  = Ticket::allStatuses();

        return view('tiket.index', compact('tikets', 'statuses'));
    }

    // ── Form Buat Tiket (user only) ───────────────────────────────────────────

    public function create()
    {
        abort_unless(auth()->user()->isUser(), 403, 'Hanya pemohon yang dapat membuat tiket.');

        $categories    = TicketCategory::active()->with('department')->orderBy('nama')->get();
        $departments   = InternalDepartment::orderBy('nama')->get();
        $jenisList     = Ticket::allJenisPengajuan();
        $prioritasList = Ticket::allPrioritas();

        return view('tiket.buat', compact('categories', 'departments', 'jenisList', 'prioritasList'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isUser(), 403);

        $data = $request->validate([
            'deskripsi'       => 'required|string|max:5000',
            'jenis_pengajuan' => 'required|in:Permintaan,Permasalahan',
            'prioritas'       => 'required|in:Rendah,Sedang,Tinggi,Kritis',
            'kategori_id'     => 'required|exists:ticket_categories,id',
            'attachment'      => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $ticket = $this->ticketService->buatTiket(
            auth()->user(),
            $data,
            $request->file('attachment')
        );

        $this->audit('CREATE', 'Sistem Tiket', 'Ticket', $ticket->id, "Membuat tiket {$ticket->nomor_tiket}");

        return redirect()->route('tiket.show', $ticket)
            ->with('status', "Tiket {$ticket->nomor_tiket} berhasil diajukan. Target respon awal: 2 jam kerja.");
    }

    // ── Detail Tiket ──────────────────────────────────────────────────────────

    public function show(Ticket $tiket)
    {
        $user = auth()->user();

        // Verifikasi visibility
        $visible = Ticket::visibleBy($user)->where('id', $tiket->id)->exists();
        abort_unless($visible, 403, 'Anda tidak memiliki akses ke tiket ini.');

        $tiket->load(['pemohon', 'kategori', 'department', 'operator', 'kabag', 'assignedStaf', 'histories.user']);

        // Data untuk form aksi kabag: daftar staf di department yang sama (multi-user)
        $stafList = collect();
        if ($user->isKabag() && $tiket->department_id) {
            $slugMap = ['umum' => 'staf_umum', 'aset' => 'staf_aset', 'pengadaan' => 'staf_pengadaan'];
            $idMap   = [1 => 'staf_umum', 2 => 'staf_aset', 3 => 'staf_pengadaan'];
            $deptSlug = $tiket->department?->slug;
            $stafRoleName = ($deptSlug && isset($slugMap[$deptSlug])) ? $slugMap[$deptSlug] : ($idMap[$tiket->department_id] ?? null);

            if ($stafRoleName) {
                $stafList = User::whereHas('role', fn ($q) => $q->where('nama', $stafRoleName))
                    ->where('is_active', true)
                    ->orderBy('nama_lengkap')
                    ->get();
            }
        }

        $departments   = $user->isOperator() ? InternalDepartment::orderBy('nama')->get() : collect();
        $jenisList     = Ticket::allJenisPengajuan();
        $prioritasList = Ticket::allPrioritas();

        return view('tiket.detail', compact('tiket', 'stafList', 'departments', 'jenisList', 'prioritasList'));
    }

    // ── Aksi: Operator Edit Klasifikasi (Prioritas, Jenis) ──────────────────

    public function updateKlasifikasi(Request $request, Ticket $tiket)
    {
        abort_unless(auth()->user()->isOperator(), 403, 'Hanya operator yang dapat mengubah klasifikasi tiket.');

        $data = $request->validate([
            'jenis_pengajuan' => 'required|in:Permintaan,Permasalahan',
            'prioritas'       => 'required|in:Rendah,Sedang,Tinggi,Kritis',
            'catatan'         => 'nullable|string|max:500',
        ]);

        $this->ticketService->updateKlasifikasi($tiket, auth()->user(), $data);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Mengubah klasifikasi tiket {$tiket->nomor_tiket}");

        return back()->with('status', "Klasifikasi & target SLA tiket {$tiket->nomor_tiket} berhasil diperbarui.");
    }

    // ── Aksi: Operator Alokasi ────────────────────────────────────────────────

    public function alokasi(Request $request, Ticket $tiket)
    {
        abort_unless(auth()->user()->isOperator(), 403);
        abort_unless($tiket->status === Ticket::STATUS_MENUNGGU, 422, 'Tiket tidak dalam status yang dapat dialokasikan.');

        $request->validate([
            'department_id'   => 'required|exists:internal_departments,id',
            'jenis_pengajuan' => 'nullable|in:Permintaan,Permasalahan',
            'prioritas'       => 'nullable|in:Rendah,Sedang,Tinggi,Kritis',
            'catatan'         => 'nullable|string|max:1000',
        ]);

        $extraData = $request->only(['jenis_pengajuan', 'prioritas', 'catatan']);
        $this->ticketService->alokasikan($tiket, auth()->user(), (int) $request->department_id, $extraData);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Mengalokasikan tiket {$tiket->nomor_tiket}");

        return back()->with('status', "Tiket {$tiket->nomor_tiket} berhasil dialokasikan (Timer Response Time dicatat).");
    }

    // ── Aksi: Kabag Setujui ───────────────────────────────────────────────────

    public function setujui(Request $request, Ticket $tiket)
    {
        $user = auth()->user();
        abort_unless($user->isKabag(), 403);
        abort_unless($tiket->status === Ticket::STATUS_DIALOKASIKAN, 422, 'Tiket tidak dalam status yang dapat disetujui.');
        abort_unless($tiket->department_id === $user->effectiveDepartmentId(), 403, 'Tiket tidak di bagian Anda.');

        $request->validate([
            'staf_id'        => 'required_without:assigned_to|nullable|exists:users,id',
            'assigned_to'    => 'required_without:staf_id|nullable|exists:users,id',
            'catatan'        => 'nullable|string|max:1000',
        ]);

        $stafId = (int) ($request->staf_id ?? $request->assigned_to);
        $this->ticketService->setujui($tiket, $user, $stafId, $request->catatan);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Menyetujui tiket {$tiket->nomor_tiket}");

        return back()->with('status', "Tiket {$tiket->nomor_tiket} disetujui dan ditugaskan ke staf. Timer Resolution Time mulai berjalan.");
    }

    // ── Aksi: Kabag Tolak ─────────────────────────────────────────────────────

    public function tolak(Request $request, Ticket $tiket)
    {
        $user = auth()->user();
        abort_unless($user->isKabag(), 403);
        abort_unless($tiket->status === Ticket::STATUS_DIALOKASIKAN, 422, 'Tiket tidak dalam status yang dapat ditolak.');
        abort_unless($tiket->department_id === $user->effectiveDepartmentId(), 403, 'Tiket tidak di bagian Anda.');

        $request->validate([
            'alasan' => 'required|string|max:1000',
        ]);

        $this->ticketService->tolak($tiket, $user, $request->alasan);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Menolak tiket {$tiket->nomor_tiket}");

        return back()->with('status', "Tiket {$tiket->nomor_tiket} telah ditolak.");
    }

    // ── Aksi: Staf Selesai ────────────────────────────────────────────────────

    public function selesai(Request $request, Ticket $tiket)
    {
        $user = auth()->user();
        abort_unless($user->isStaf(), 403);
        abort_unless($tiket->status === Ticket::STATUS_DALAM_PROSES, 422, 'Tiket tidak dalam proses.');
        abort_unless($tiket->assigned_to === $user->id || $tiket->department_id === $user->effectiveDepartmentId(), 403);

        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $this->ticketService->selesaikan($tiket, $user, $request->catatan);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Menyelesaikan tiket {$tiket->nomor_tiket}");

        return back()->with('status', "Tiket {$tiket->nomor_tiket} berhasil diselesaikan.");
    }

    // ── Aksi: Pemohon Tutup ───────────────────────────────────────────────────

    public function tutup(Request $request, Ticket $tiket)
    {
        $user = auth()->user();
        abort_unless($user->isUser(), 403);
        abort_unless($tiket->pemohon_id === $user->id, 403, 'Hanya pemohon yang dapat menutup tiket ini.');
        abort_unless(in_array($tiket->status, [Ticket::STATUS_SELESAI, Ticket::STATUS_DITOLAK]), 422, 'Tiket belum selesai atau ditolak.');

        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $this->ticketService->tutup($tiket, $user, $request->catatan);
        $this->audit('UPDATE', 'Sistem Tiket', 'Ticket', $tiket->id, "Menutup tiket {$tiket->nomor_tiket}");

        return redirect()->route('tiket.index')
            ->with('status', "Tiket {$tiket->nomor_tiket} berhasil ditutup. Terima kasih.");
    }

    // ── Pratinjau & Download Lampiran ─────────────────────────────────────────

    public function viewAttachment(Ticket $tiket)
    {
        $user = auth()->user();

        // Pastikan user bisa melihat tiket ini
        $visible = Ticket::visibleBy($user)->where('id', $tiket->id)->exists();
        abort_unless($visible, 403, 'Anda tidak memiliki akses ke lampiran tiket ini.');
        abort_unless($tiket->attachment, 404, 'Lampiran tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($tiket->attachment), 404, 'File lampiran tidak ditemukan.');

        $path = Storage::disk('public')->path($tiket->attachment);
        $mimeType = Storage::disk('public')->mimeType($tiket->attachment) ?: 'application/octet-stream';
        $filename = basename($tiket->attachment);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function downloadAttachment(Ticket $tiket)
    {
        $user = auth()->user();

        // Pastikan user bisa melihat tiket ini
        $visible = Ticket::visibleBy($user)->where('id', $tiket->id)->exists();
        abort_unless($visible, 403, 'Anda tidak memiliki akses ke lampiran tiket ini.');
        abort_unless($tiket->attachment, 404, 'Lampiran tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($tiket->attachment), 404, 'File lampiran tidak ditemukan.');

        return Storage::disk('public')->download($tiket->attachment, basename($tiket->attachment));
    }
}