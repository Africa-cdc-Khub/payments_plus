<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationParticipant extends Model
{
    protected $table = 'registration_participants';

    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'nationality',
        'national_id',
        'passport_number',
        'organization',
        'passport_file',
        'requires_visa',
        'institution',
        'student_id_file',
        'delegate_category',
        'airport_of_origin',
        'attendance_status',
        'attendance_verified_at',
        'verified_by',
    ];

    protected $casts = [
        'requires_visa' => 'boolean',
        'attendance_verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}

