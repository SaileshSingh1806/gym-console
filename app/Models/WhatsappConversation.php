<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'phone',
        'last_message_at',
        'last_message_preview',
        'last_incoming_at',
        'unread_count',
        'status',
        'assigned_to_user_id',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_incoming_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'contact_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'conversation_id')->oldest();
    }

    /**
     * Check if the Meta 24-hour customer service window is currently open.
     */
    public function isCustomerServiceWindowOpen(): bool
    {
        if (! $this->last_incoming_at) {
            return false;
        }

        return $this->last_incoming_at->greaterThan(Carbon::now()->subHours(24));
    }

    /**
     * Get remaining minutes/seconds in the 24-hour service window.
     */
    public function remainingWindowSeconds(): int
    {
        if (! $this->isCustomerServiceWindowOpen()) {
            return 0;
        }

        $expiresAt = $this->last_incoming_at->copy()->addHours(24);

        return max(0, Carbon::now()->diffInSeconds($expiresAt, false));
    }
}

