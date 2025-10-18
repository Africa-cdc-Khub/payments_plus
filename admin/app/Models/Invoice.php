<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'biller_name',
        'biller_email',
        'biller_address',
        'item',
        'description',
        'quantity',
        'rate',
        'amount',
        'currency',
        'status',
        'created_by',
        'paid_at',
        'paid_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(Admin::class, 'paid_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(Admin::class, 'cancelled_by');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        $class = $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $class . '">' . 
               ucfirst($this->status) . '</span>';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
