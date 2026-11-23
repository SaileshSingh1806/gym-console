<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberAgreement extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    public const STATUS_NOT_SENT = 'NOT_SENT';
    public const STATUS_LINK_SENT = 'LINK_SENT';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_MEDICAL_REVIEW = 'MEDICAL_REVIEW_REQUIRED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_DECLINED = 'DECLINED';

    protected $fillable = [
        'tenant_id',
        'member_id',
        'agreement_template_id',
        'version',
        'token',
        'token_hash',
        'token_expires_at',
        'status',
        'link_sent_at',
        'link_sent_channel',
        'otp_code',
        'otp_expires_at',
        'otp_verified_at',
        'otp_attempts',
        'profile_confirmed_at',
        'health_declaration_answers',
        'health_declaration_accepted_at',
        'has_medical_conditions',
        'medical_review_notes',
        'reviewed_by_user_id',
        'reviewed_at',
        'terms_accepted_at',
        'terrace_rules_included',
        'terrace_declaration_accepted_at',
        'signature_type',
        'signature_data',
        'signature_provider',
        'signature_reference',
        'signature_hash',
        'signed_at',
        'signer_ip',
        'signer_user_agent',
        'pdf_path',
        'declined_reason',
        'declined_at',
        'audit_trail',
    ];

    protected $casts = [
        'version' => 'integer',
        'token_expires_at' => 'datetime',
        'link_sent_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'otp_attempts' => 'integer',
        'profile_confirmed_at' => 'datetime',
        'health_declaration_answers' => 'array',
        'health_declaration_accepted_at' => 'datetime',
        'has_medical_conditions' => 'boolean',
        'reviewed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'terrace_rules_included' => 'boolean',
        'terrace_declaration_accepted_at' => 'datetime',
        'signed_at' => 'datetime',
        'declined_at' => 'datetime',
        'audit_trail' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TenantAgreementTemplate::class, 'agreement_template_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_LINK_SENT, self::STATUS_NOT_SENT]);
    }

    public function requiresMedicalReview(): bool
    {
        return $this->status === self::STATUS_MEDICAL_REVIEW || ($this->has_medical_conditions && ! $this->reviewed_at);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || ($this->token_expires_at && $this->token_expires_at->isPast() && ! $this->isCompleted());
    }

    /**
     * Get secure public link for member signing.
     */
    public function getShareUrlAttribute(): string
    {
        return route('public.agreement.show', ['token' => $this->token]);
    }

    /**
     * Get WhatsApp share link with prefilled polite, professional message.
     */
    public function getWhatsAppShareUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->member?->phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        $gymName = $this->tenant?->name ?? 'GymDeskHub';
        $memberName = $this->member?->first_name ?? 'Member';
        $url = $this->share_url;

        $msg = "Dear {$memberName},\n\nWelcome to {$gymName}!\n\nPlease complete your digital Membership Agreement and Health Declaration by clicking your secure personal link below:\n\n👉 {$url}\n\nIt takes less than 2 minutes on your mobile.\n\nWarm regards,\n{$gymName} Team";

        return "https://api.whatsapp.com/send?phone={$phone}&text=" . urlencode($msg);
    }

    /**
     * Get SMS share link.
     */
    public function getSmsShareUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->member?->phone);
        $gymName = $this->tenant?->name ?? 'GymDeskHub';
        $url = $this->share_url;

        $msg = "Dear {$this->member?->first_name}, please complete your digital membership agreement for {$gymName} here: {$url}";

        return "sms:{$phone}?body=" . urlencode($msg);
    }

    /**
     * Record an immutable audit log entry into JSON audit trail.
     */
    public function recordAuditEvent(string $event, array $context = []): void
    {
        $trail = $this->audit_trail ?? [];
        $trail[] = array_merge([
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip() ?? null,
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ], $context);

        $this->audit_trail = $trail;
    }

    /**
     * Generate unique signature audit transaction reference.
     */
    public static function generateSignatureReference(int $tenantId, int $memberId): string
    {
        $random = strtoupper(bin2hex(random_bytes(3)));
        $year = date('Y');

        return "GDH-AGR-{$year}-T{$tenantId}M{$memberId}-{$random}";
    }
}
