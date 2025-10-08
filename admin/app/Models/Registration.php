<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    protected $table = 'registrations';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'package_id',
        'registration_type',
        'total_amount',
        'currency',
        'status',
        'rejection_reason',
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

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'payment_completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RegistrationParticipant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }
}

