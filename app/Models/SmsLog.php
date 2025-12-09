<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsLog extends Model
{
    protected $fillable = [
        'phone',
        'message',
        'sender',
        'message_type',
        'message_content_type',
        'status',
        'response_id',
        'quantity',
        'amount',
        'number_count',
        'description',
        'response_data',
        'invalid_phones',
        'notifiable_type',
        'notifiable_id',
        'bulk_sms_id',
        'sent_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'response_data' => 'array',
            'invalid_phones' => 'array',
            'quantity' => 'integer',
            'amount' => 'decimal:2',
            'number_count' => 'integer',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function bulkSms(): BelongsTo
    {
        return $this->belongsTo(BulkSms::class, 'bulk_sms_id');
    }
}
