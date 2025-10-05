<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationParticipant extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'registration_participants';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'nationality',
        'passport_number',
        'organization',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the registration that owns the participant
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
