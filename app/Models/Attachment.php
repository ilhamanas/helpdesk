<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfm_id',
        'file_name',
        'file_path',
        'uploaded_by',
    ];

    public function rfm()
    {
        return $this->belongsTo(Rfm::class, 'rfm_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
