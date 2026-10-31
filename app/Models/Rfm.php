<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfm extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfm_number',
        'user_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'reviewed_at',
        'review_rating',
        'review_comment',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'rfm_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'rfm_id');
    }

    /** Tiket selesai dikerjakan tapi belum direview karyawan. */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'resolved')->whereNull('reviewed_at');
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'resolved' && $this->reviewed_at === null;
    }

    /** Nomor dokumen berikutnya, mis. RFM/IT/0006. Naik terus, tidak reset bulanan. */
    public static function nextNumber(string $prefix = 'RFM/IT/'): string
    {
        $last = static::where('rfm_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(rfm_number, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
            ->value('rfm_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
