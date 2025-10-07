<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'title',
        'phone',
        'nationality',
        'national_id',
        'organization',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'passport_number',
        'passport_file',
        'requires_visa',
        'position',
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

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
