<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'attendance_percentage',
        'neatness_score',
        'final_score'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'attendance_percentage' => 'float',
        'neatness_score' => 'float',
        'final_score' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
