<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'amount',
        'currency',
        'transaction_uuid',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'payment_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the registration that owns the payment
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }
}
