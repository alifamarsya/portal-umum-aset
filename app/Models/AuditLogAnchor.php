<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogAnchor extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'root_hash',
        'tx_hash',
        'block_number',
        'periode_awal',
        'periode_akhir',
        'status',
        'error_message',
        'total_logs',
        'anchored_at',
    ];

    protected $casts = [
        'periode_awal'  => 'date',
        'periode_akhir' => 'date',
        'anchored_at'   => 'datetime',
        'total_logs'    => 'integer',
        'block_number'  => 'integer',
    ];

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::STATUS_FAILED  => 'bg-rose-50 text-rose-700 border-rose-200',
            default              => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'Tersimpan di Sepolia',
            self::STATUS_PENDING => 'Sedang Diproses',
            self::STATUS_FAILED  => 'Gagal Anchoring',
            default              => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function etherscanUrl(): ?string
    {
        if (!$this->tx_hash) {
            return null;
        }
        return 'https://sepolia.etherscan.io/tx/' . $this->tx_hash;
    }
}