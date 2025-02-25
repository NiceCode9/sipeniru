<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'presentasi_absensi',
        'score_kerapian',
        'score_akhir',
        'predikat',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'presentasi_absensi' => 'float',
        'score_kerapian' => 'float',
        'score_akhir' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
