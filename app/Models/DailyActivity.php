<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rfm_id',
        'activity_type',
        'description',
        'duration_minutes',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rfm()
    {
        return $this->belongsTo(Rfm::class, 'rfm_id');
    }
}
