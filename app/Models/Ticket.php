<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    // Status constants
    const STATUS_MENUNGGU    = 'Menunggu Verifikasi';
    const STATUS_DIALOKASIKAN = 'Dialokasikan';
    const STATUS_DALAM_PROSES = 'Dalam Proses';
    const STATUS_SELESAI      = 'Selesai';
    const STATUS_DITOLAK      = 'Ditolak';
    const STATUS_DITUTUP      = 'Ditutup';

    // Jenis Pengajuan constants
    const JENIS_PERMINTAAN   = 'Permintaan';
    const JENIS_PERMASALAHAN = 'Permasalahan';

    // Prioritas constants
    const PRIORITAS_RENDAH = 'Rendah';
    const PRIORITAS_SEDANG = 'Sedang';
    const PRIORITAS_TINGGI = 'Tinggi';
    const PRIORITAS_KRITIS = 'Kritis';

    // SLA hours mapping
    const SLA_HOURS = [
        self::PRIORITAS_RENDAH => 72,
        self::PRIORITAS_SEDANG => 48,
        self::PRIORITAS_TINGGI => 12,
        self::PRIORITAS_KRITIS => 4,
    ];

    // Skala constants
    const SKALA_KECIL  = 'Kecil';
    const SKALA_SEDANG = 'Sedang';
    const SKALA_BESAR  = 'Besar';

    protected $fillable = [
        'nomor_tiket',
        'judul',
        'deskripsi',
        'jenis_pengajuan',
        'prioritas',
        'sla_jam',
        'sla_due_at',
        'response_due_at',
        'responded_at',
        'response_sla_status',
        'skala',
        'estimasi_biaya',
        'resolution_started_at',
        'resolution_hours',
        'resolution_due_at',
        'resolved_at',
        'resolution_sla_status',
        'sla_status',
        'kategori_id',
        'pemohon_id',
        'department_id',
        'operator_id',
        'kabag_id',
        'assigned_to',
        'status',
        'alasan_penolakan',
        'attachment',
    ];

    protected $casts = [
        'sla_due_at'            => 'datetime',
        'sla_jam'               => 'integer',
        'response_due_at'       => 'datetime',
        'responded_at'          => 'datetime',
        'resolution_started_at' => 'datetime',
        'resolution_due_at'     => 'datetime',
        'resolved_at'           => 'datetime',
        'resolution_hours'      => 'integer',
        'estimasi_biaya'        => 'decimal:2',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function kategori()
    {
        return $this->belongsTo(TicketCategory::class, 'kategori_id');
    }

    public function department()
    {
        return $this->belongsTo(InternalDepartment::class, 'department_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function kabag()
    {
        return $this->belongsTo(User::class, 'kabag_id');
    }

    public function assignedStaf()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories()
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id')->orderBy('id');
    }

    // ── Scopes (pembatasan visibility) ──────────────────────────────────────

    /**
     * Filter tiket berdasarkan role yang sedang login.
     */
    public function scopeVisibleBy($query, User $user)
    {
        $role = $user->role?->nama;

        return match ($role) {
            'admin', 'pimpinan'  => $query,                             // lihat semua
            'operator'           => $query,                             // lihat semua (perlu alokasi)
            'user'               => $query->where('pemohon_id', $user->id),
            'kabag_umum'         => $query->where('department_id', 1),
            'kabag_aset'         => $query->where('department_id', 2),
            'kabag_pengadaan'    => $query->where('department_id', 3),
            'staf_umum'          => $query->where('department_id', 1)->whereNotNull('assigned_to'),
            'staf_aset'          => $query->where('department_id', 2)->whereNotNull('assigned_to'),
            'staf_pengadaan'     => $query->where('department_id', 3)->whereNotNull('assigned_to'),
            default              => $query->whereRaw('1 = 0'),         // tidak boleh lihat apa-apa
        };
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Badge warna CSS per status (Tailwind classes).
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU    => 'bg-amber-100 text-amber-800',
            self::STATUS_DIALOKASIKAN => 'bg-blue-100 text-blue-800',
            self::STATUS_DALAM_PROSES => 'bg-indigo-100 text-indigo-800',
            self::STATUS_SELESAI      => 'bg-emerald-100 text-emerald-800',
            self::STATUS_DITOLAK      => 'bg-rose-100 text-rose-800',
            self::STATUS_DITUTUP      => 'bg-slate-100 text-slate-600',
            default                   => 'bg-gray-100 text-gray-600',
        };
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU    => 'clock',
            self::STATUS_DIALOKASIKAN => 'arrow-right',
            self::STATUS_DALAM_PROSES => 'loader',
            self::STATUS_SELESAI      => 'check-circle',
            self::STATUS_DITOLAK      => 'x-circle',
            self::STATUS_DITUTUP      => 'archive',
            default                   => 'file-text',
        };
    }

    public static function allStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU,
            self::STATUS_DIALOKASIKAN,
            self::STATUS_DALAM_PROSES,
            self::STATUS_SELESAI,
            self::STATUS_DITOLAK,
            self::STATUS_DITUTUP,
        ];
    }

    public static function allJenisPengajuan(): array
    {
        return [
            self::JENIS_PERMINTAAN,
            self::JENIS_PERMASALAHAN,
        ];
    }

    public static function allPrioritas(): array
    {
        return [
            self::PRIORITAS_RENDAH,
            self::PRIORITAS_SEDANG,
            self::PRIORITAS_TINGGI,
            self::PRIORITAS_KRITIS,
        ];
    }

    public static function prioritasLabelWithSla(string $prioritas): string
    {
        $jam = self::SLA_HOURS[$prioritas] ?? 48;
        return "{$prioritas} (SLA {$jam} Jam)";
    }

    public function prioritasBadgeClass(): string
    {
        return match ($this->prioritas) {
            self::PRIORITAS_KRITIS => 'bg-rose-100 text-rose-800 border-rose-200',
            self::PRIORITAS_TINGGI => 'bg-amber-100 text-amber-800 border-amber-200',
            self::PRIORITAS_SEDANG => 'bg-blue-100 text-blue-800 border-blue-200',
            self::PRIORITAS_RENDAH => 'bg-slate-100 text-slate-700 border-slate-200',
            default                => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function jenisBadgeClass(): string
    {
        return match ($this->jenis_pengajuan) {
            self::JENIS_PERMASALAHAN => 'bg-purple-100 text-purple-800 border-purple-200',
            default                  => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        };
    }

    public static function allSkala(): array
    {
        return [
            self::SKALA_KECIL,
            self::SKALA_SEDANG,
            self::SKALA_BESAR,
        ];
    }

    public function isSlaBreached(): bool
    {
        if (in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DITUTUP])) {
            return $this->sla_status === 'overdue' || $this->response_sla_status === 'breached' || $this->resolution_sla_status === 'breached';
        }

        if ($this->response_due_at && !$this->responded_at && now()->gt($this->response_due_at)) {
            return true;
        }

        if ($this->resolution_due_at && !$this->resolved_at && now()->gt($this->resolution_due_at)) {
            return true;
        }

        return $this->sla_due_at ? now()->greaterThan($this->sla_due_at) : false;
    }

    public function slaRemainingFormatted(): string
    {
        $targetDue = $this->resolution_due_at ?? $this->sla_due_at ?? $this->response_due_at;

        if (!$targetDue) {
            return '—';
        }

        if (in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DITUTUP])) {
            return 'Selesai';
        }

        if (now()->gt($targetDue)) {
            return 'Lewat ' . now()->diffForHumans($targetDue, true);
        }

        return now()->diffForHumans($targetDue, [
            'parts' => 2,
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
        ]) . ' lagi';
    }

    // ── Response SLA Helpers ────────────────────────────────────────────────

    public function isResponseBreached(): bool
    {
        if ($this->responded_at && $this->response_due_at) {
            return $this->responded_at->gt($this->response_due_at);
        }
        if (!$this->responded_at && $this->response_due_at) {
            return now()->gt($this->response_due_at);
        }
        return false;
    }

    public function responseSlaBadgeClass(): string
    {
        if ($this->response_sla_status === 'met' || ($this->responded_at && !$this->isResponseBreached())) {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }
        if ($this->response_sla_status === 'breached' || $this->isResponseBreached()) {
            return 'bg-rose-50 text-rose-700 border-rose-200 font-bold';
        }
        return 'bg-amber-50 text-amber-700 border-amber-200';
    }

    public function responseSlaText(): string
    {
        if ($this->responded_at) {
            return $this->isResponseBreached() ? 'Terlambat Diteruskan' : 'Tepat Waktu';
        }
        if ($this->response_due_at && now()->gt($this->response_due_at)) {
            return 'Lewat Batas Respon';
        }
        return 'Menunggu Respon';
    }

    public function responseRemainingFormatted(): string
    {
        if ($this->responded_at) {
            return 'Direspons ' . $this->responded_at->format('d/m H:i');
        }
        if (!$this->response_due_at) {
            return '—';
        }
        if (now()->gt($this->response_due_at)) {
            return 'Lewat ' . now()->diffForHumans($this->response_due_at, true);
        }
        return now()->diffForHumans($this->response_due_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) . ' lagi';
    }

    // ── Resolution SLA Helpers ──────────────────────────────────────────────

    public function isResolutionBreached(): bool
    {
        if ($this->resolved_at && $this->resolution_due_at) {
            return $this->resolved_at->gt($this->resolution_due_at);
        }
        if (!$this->resolved_at && $this->resolution_due_at) {
            return now()->gt($this->resolution_due_at);
        }
        return false;
    }

    public function resolutionSlaBadgeClass(): string
    {
        if ($this->resolution_sla_status === 'met' || ($this->resolved_at && !$this->isResolutionBreached())) {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }
        if ($this->resolution_sla_status === 'breached' || $this->isResolutionBreached()) {
            return 'bg-rose-50 text-rose-700 border-rose-200 font-bold';
        }
        if ($this->resolution_due_at) {
            return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        }
        return 'bg-slate-100 text-slate-600 border-slate-200';
    }

    public function resolutionSlaText(): string
    {
        if ($this->resolved_at) {
            return $this->isResolutionBreached() ? 'Selesai Terlambat' : 'Selesai Tepat Waktu';
        }
        if (!$this->resolution_due_at) {
            return 'Menunggu Persetujuan Kabag';
        }
        if (now()->gt($this->resolution_due_at)) {
            return 'Lewat Batas Resolusi';
        }
        return 'Dalam Pengerjaan (' . ($this->resolution_hours ?? 0) . 'j kerja)';
    }

    public function resolutionRemainingFormatted(): string
    {
        if ($this->resolved_at) {
            return 'Selesai ' . $this->resolved_at->format('d/m H:i');
        }
        if (!$this->resolution_due_at) {
            return 'Menunggu persetujuan';
        }
        if (now()->gt($this->resolution_due_at)) {
            return 'Lewat ' . now()->diffForHumans($this->resolution_due_at, true);
        }
        return now()->diffForHumans($this->resolution_due_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) . ' lagi';
    }

    public function formattedEstimasiBiaya(): string
    {
        return 'Rp ' . number_format($this->estimasi_biaya ?? 0, 0, ',', '.');
    }
}