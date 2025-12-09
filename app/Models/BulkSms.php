<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkSms extends Model
{
    protected $table = 'bulk_sms';

    protected $fillable = [
        'name',
        'message',
        'sender',
        'message_type',
        'message_content_type',
        'target_type',
        'target_ids',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'created_by',
        'sent_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_ids' => 'array',
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'bulk_sms_id');
    }
}
