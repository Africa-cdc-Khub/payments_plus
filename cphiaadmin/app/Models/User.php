<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'title',
        'phone',
        'nationality',
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

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'attendance_verified_at' => 'datetime',
            'requires_visa' => 'boolean',
        ];
    }

    /**
     * Get all registrations for this user
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scope for users requiring visa
     */
    public function scopeRequiresVisa($query)
    {
        return $query->where('requires_visa', true);
    }

    /**
     * Scope for African nationals
     */
    public function scopeAfricanNationals($query)
    {
        $africanCountries = [
            'Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi',
            'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros',
            'Congo', 'Democratic Republic of Congo', 'Djibouti', 'Egypt',
            'Equatorial Guinea', 'Eritrea', 'Eswatini', 'Ethiopia', 'Gabon',
            'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau', 'Ivory Coast', 'Kenya',
            'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi', 'Mali',
            'Mauritania', 'Mauritius', 'Morocco', 'Mozambique', 'Namibia', 'Niger',
            'Nigeria', 'Rwanda', 'Sao Tome and Principe', 'Senegal', 'Seychelles',
            'Sierra Leone', 'Somalia', 'South Africa', 'South Sudan', 'Sudan',
            'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Zambia', 'Zimbabwe'
        ];

        return $query->whereIn('country', $africanCountries);
    }

    /**
     * Scope for attendance status
     */
    public function scopeByAttendanceStatus($query, $status)
    {
        return $query->where('attendance_status', $status);
    }
}
