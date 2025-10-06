<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'registrations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'registration_type',
        'total_amount',
        'currency',
        'status',
        'payment_status',
        'payment_completed_at',
        'payment_transaction_id',
        'payment_amount',
        'payment_currency',
        'payment_method',
        'payment_reference',
        'payment_token',
        'exhibition_description',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'payment_completed_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the registration
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package for the registration
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get all payments for this registration
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all participants for this registration
     */
    public function participants()
    {
        return $this->hasMany(RegistrationParticipant::class);
    }

    /**
     * Scope for paid registrations
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for pending registrations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope by registration type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('registration_type', $type);
    }

    /**
     * Scope by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }
}
