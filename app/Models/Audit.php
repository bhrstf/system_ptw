<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $table = 'audits';

    protected $fillable = [
        'permit_id',
        'finding',
        'status',
    ];

    /**
     * Relasi ke Permit: Setiap Audit dimiliki oleh satu Permit
     */
    public function permit()
    {
        return $this->belongsTo(Permit::class, 'permit_id');
    }
}