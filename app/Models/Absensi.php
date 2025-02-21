<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'check_in',
        'check_out',
        'nilai_kerapian',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'neatness_score' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
