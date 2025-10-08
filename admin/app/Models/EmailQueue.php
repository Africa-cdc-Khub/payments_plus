<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    protected $table = 'email_queue';

    protected $fillable = [
        'to_email',
        'to_name',
        'subject',
        'template_name',
        'template_data',
        'body_html',
        'body_text',
        'email_type',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'error_message',
        'sent_at',
        'next_attempt_at',
    ];

    protected $casts = [
        'template_data' => 'array',
        'priority' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'sent_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to get pending emails
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('next_attempt_at')
                  ->orWhere('next_attempt_at', '<=', now());
            });
    }

    /**
     * Scope to get failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get sent emails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('created_at', 'asc');
    }

    /**
     * Mark email as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed($errorMessage)
    {
        $attempts = $this->attempts + 1;
        $status = $attempts >= $this->max_attempts ? 'failed' : 'pending';
        
        $this->update([
            'status' => $status,
            'attempts' => $attempts,
            'error_message' => $errorMessage,
            'next_attempt_at' => $status === 'pending' ? now()->addMinutes(5 * $attempts) : null,
        ]);
    }

    /**
     * Check if email can be retried
     */
    public function canRetry()
    {
        return $this->attempts < $this->max_attempts;
    }
}

