<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalDepartment extends Model
{
    protected $table = 'internal_departments';

    protected $fillable = ['nama', 'slug', 'deskripsi'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'department_id');
    }

    public function ticketCategories()
    {
        return $this->hasMany(TicketCategory::class, 'department_id');
    }
}