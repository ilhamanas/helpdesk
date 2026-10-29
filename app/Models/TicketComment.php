<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfm_id',
        'user_id',
        'comment',
    ];

    public function rfm()
    {
        return $this->belongsTo(Rfm::class, 'rfm_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
