<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'payload',
        'processed',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}

