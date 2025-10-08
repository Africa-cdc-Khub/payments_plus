<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Delegate Package ID
    |--------------------------------------------------------------------------
    |
    | The package ID that identifies delegate registrations
    |
    */
    
    'package_id' => env('DELEGATE_PACKAGE_ID', 29),
    
    /*
    |--------------------------------------------------------------------------
    | Fully Sponsored Categories
    |--------------------------------------------------------------------------
    |
    | Delegate categories that receive full sponsorship
    | (Visa, Accommodation, and Registration fees covered)
    |
    */
    
    'fully_sponsored_categories' => [
        'Oral abstract presenter',
        'Invited speaker/Moderator',
        'Scientific Program Committee Member',
        'Secretariat',
        'Media Partner',
        'Youth Program Participant',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | All Delegate Categories
    |--------------------------------------------------------------------------
    |
    | Complete list of available delegate categories
    | These categories are set during user registration
    |
    */
    
    'categories' => [
        'Oral abstract presenter',
        'Poster presenter',
        'Invited speaker/Moderator',
        'Scientific Program Committee Member',
        'Secretariat',
        'Media Partner',
        'Youth Program Participant',
        'Government Official',
        'NGO Representative',
        'Academic/Researcher',
        'Healthcare Professional',
        'Private Sector',
        'Student',
        'Other',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Status Options
    |--------------------------------------------------------------------------
    |
    | Available statuses for delegate applications
    |
    */
    
    'statuses' => [
        'pending' => [
            'label' => 'Pending Review',
            'color' => 'yellow',
            'icon' => 'clock',
        ],
        'approved' => [
            'label' => 'Approved',
            'color' => 'green',
            'icon' => 'check-circle',
        ],
        'rejected' => [
            'label' => 'Rejected',
            'color' => 'red',
            'icon' => 'times-circle',
        ],
    ],
    
];

