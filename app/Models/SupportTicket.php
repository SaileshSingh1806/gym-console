<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'tenant_id',
        'user_id',
        'subject',
        'category',
        'priority',
        'status',
        'last_reply_at',
        'last_reply_by_user_id',
        'resolved_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static function generateTicketNumber(): string
    {
        $year = now()->format('Y');
        $random = strtoupper(substr(uniqid(), -4));
        $count = self::withoutGlobalScopes()->count() + 1;

        return sprintf('TCK-%s-%04d%s', $year, $count, $random);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastReplyBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reply_by_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id')->orderBy('created_at', 'asc');
    }

    public function latestReply()
    {
        return $this->hasOne(SupportTicketReply::class, 'support_ticket_id')->latestOfMany();
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match (strtolower($this->priority)) {
            'urgent' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'high' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'medium' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            default => 'bg-slate-800 text-slate-400 border-slate-700',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match (strtolower($this->status)) {
            'open' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'in_progress' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'answered' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
            'resolved' => 'bg-teal-500/20 text-teal-400 border-teal-500/30',
            'closed' => 'bg-slate-800 text-slate-400 border-slate-700',
            default => 'bg-slate-800 text-slate-400 border-slate-700',
        };
    }
}
