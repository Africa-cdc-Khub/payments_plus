<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'amount',
        'currency',
        'transaction_uuid',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'completed_by',
        'manual_payment_remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'completed_by');
    }
}

