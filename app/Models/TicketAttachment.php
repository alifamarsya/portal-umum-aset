<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function isImage(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function iconName(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => 'file-text',
            'doc', 'docx' => 'file-text',
            'xls', 'xlsx' => 'table',
            'jpg', 'jpeg', 'png', 'webp' => 'image',
            default => 'file',
        };
    }
}
