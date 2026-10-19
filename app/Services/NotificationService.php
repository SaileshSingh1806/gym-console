<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Member;
use App\Models\Membership;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappConversation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get real-time operational notifications feed for the current user/tenant.
     */
    public function getFeed(User $user): array
    {
        $tenant = TenantContext::getTenant() ?? $user->tenant;
        $readIds = $user->metadata['read_notifications'] ?? [];
        if (! is_array($readIds)) {
            $readIds = [];
        }

        $items = [];

        // 1. Super Admin Platform Notifications (if Super Admin viewing without tenant or on global context)
        if ($user->isSuperAdmin() && ! $tenant) {
            $openTickets = SupportTicket::where('status', 'OPEN')->latest()->take(5)->get();
            foreach ($openTickets as $ticket) {
                $items[] = [
                    'id' => "ticket_{$ticket->id}_{$ticket->updated_at?->timestamp}",
                    'type' => 'ticket',
                    'category' => 'Support',
                    'title' => "Support Ticket: " . Str::limit($ticket->subject ?? 'New Ticket', 35),
                    'message' => "From gym #{$ticket->tenant_id}: " . Str::limit($ticket->description ?? '', 60),
                    'time_ago' => $ticket->created_at?->diffForHumans() ?? 'Recently',
                    'timestamp' => $ticket->created_at?->timestamp ?? 0,
                    'link' => route('admin.support'),
                    'action_label' => 'View Ticket',
                    'color' => 'amber',
                    'icon' => 'ticket',
                ];
            }

            $recentTenants = Tenant::where('created_at', '>=', Carbon::now()->subDays(3))->latest()->take(5)->get();
            foreach ($recentTenants as $t) {
                $items[] = [
                    'id' => "tenant_{$t->id}",
                    'type' => 'tenant',
                    'category' => 'Gyms',
                    'title' => "New Gym Registered: {$t->name}",
                    'message' => "Status: {$t->status} • Currency: {$t->currency}",
                    'time_ago' => $t->created_at?->diffForHumans() ?? 'Recently',
                    'timestamp' => $t->created_at?->timestamp ?? 0,
                    'link' => route('admin.gyms'),
                    'action_label' => 'Inspect Gym',
                    'color' => 'emerald',
                    'icon' => 'building',
                ];
            }
        }

        // 2. Tenant Gym Notifications
        if ($tenant) {
            // A. WhatsApp Inbound Messages (Unread customer replies)
            if ($user->hasPermission('whatsapp.inbox') && $user->hasFeature('whatsapp_marketing')) {
                $unreadConvs = WhatsappConversation::where('tenant_id', $tenant->id)
                    ->where('unread_count', '>', 0)
                    ->with('contact')
                    ->latest('last_incoming_at')
                    ->take(5)
                    ->get();

                foreach ($unreadConvs as $conv) {
                    $senderName = $conv->contact?->name ?: ($conv->phone ?: 'Customer');
                    $items[] = [
                        'id' => "wa_conv_{$conv->id}_{$conv->last_incoming_at?->timestamp}",
                        'type' => 'whatsapp',
                        'category' => 'WhatsApp',
                        'title' => "💬 Reply from {$senderName}",
                        'message' => Str::limit($conv->last_message_preview ?: 'Incoming WhatsApp message awaiting reply', 70),
                        'time_ago' => $conv->last_incoming_at?->diffForHumans() ?? 'Recently',
                        'timestamp' => $conv->last_incoming_at?->timestamp ?? 0,
                        'link' => route('app.whatsapp.inbox') . '?chat=' . $conv->id,
                        'action_label' => 'Reply in Inbox',
                        'color' => 'emerald',
                        'icon' => 'chat',
                    ];
                }
            }

            // B. Memberships Expiring Soon or Recently Expired (Within 7 days)
            if ($user->hasPermission('memberships.view')) {
                $expiringMemberships = Membership::where('tenant_id', $tenant->id)
                    ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                    ->whereBetween('end_date', [Carbon::today()->subDays(7)->startOfDay(), Carbon::today()->addDays(7)->endOfDay()])
                    ->with(['member', 'plan'])
                    ->orderBy('end_date')
                    ->take(5)
                    ->get();

                foreach ($expiringMemberships as $m) {
                    $memberName = $m->member?->full_name ?: 'Gym Member';
                    $planName = $m->plan?->name ?: 'Membership';
                    $daysRemaining = Carbon::today()->diffInDays($m->end_date, false);
                    $daysText = $daysRemaining < 0 ? abs($daysRemaining) . ' days ago' : ($daysRemaining === 0 ? 'Today' : ($daysRemaining === 1 ? 'Tomorrow' : "In {$daysRemaining} days"));
                    $isExpired = $daysRemaining < 0;

                    $items[] = [
                        'id' => "exp_mem_{$m->id}_{$m->end_date->format('Ymd')}",
                        'type' => 'renewal',
                        'category' => 'Renewals',
                        'title' => ($isExpired ? '🚨 Expired Membership: ' : '⏳ Expiry Alert: ') . $memberName,
                        'message' => "{$planName} " . ($isExpired ? 'expired ' : 'expires ') . "{$daysText} ({$m->end_date->format('d M')}). Prompt for renewal.",
                        'time_ago' => $daysText,
                        'timestamp' => $m->end_date->timestamp,
                        'link' => route('app.members.show', $m->member_id) . '?tab=membership',
                        'action_label' => 'Renew Plan',
                        'color' => $isExpired ? 'rose' : 'amber',
                        'icon' => 'clock',
                    ];
                }
            }

            // C. Pending Dues / Fee Collection Alerts
            if ($user->hasPermission('payments.view')) {
                $pendingDues = Membership::where('tenant_id', $tenant->id)
                    ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                    ->whereRaw('final_amount > paid_amount')
                    ->with(['member', 'plan'])
                    ->orderByRaw('(final_amount - paid_amount) DESC')
                    ->take(5)
                    ->get();

                foreach ($pendingDues as $due) {
                    $memberName = $due->member?->full_name ?: 'Gym Member';
                    $balance = (float) ($due->final_amount - $due->paid_amount);

                    $items[] = [
                        'id' => "due_mem_{$due->id}_" . (int) $balance,
                        'type' => 'payment',
                        'category' => 'Payments',
                        'title' => "💰 Pending Due: {$memberName}",
                        'message' => "₹" . number_format($balance, 0) . " unpaid balance on {$due->plan?->name}. Collect fee.",
                        'time_ago' => 'Outstanding',
                        'timestamp' => $due->updated_at?->timestamp ?? 0,
                        'link' => route('app.members.show', $due->member_id) . '?tab=payments',
                        'action_label' => 'Collect Fee',
                        'color' => 'rose',
                        'icon' => 'currency',
                    ];
                }
            }

            // D. Today's Member Birthdays
            if ($user->hasPermission('members.view')) {
                $todayBirthdays = Member::where('tenant_id', $tenant->id)
                    ->where('status', 'ACTIVE')
                    ->whereMonth('dob', Carbon::today()->month)
                    ->whereDay('dob', Carbon::today()->day)
                    ->take(5)
                    ->get();

                foreach ($todayBirthdays as $bMember) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string) $bMember->phone);
                    $wishText = urlencode("Happy Birthday {$bMember->first_name}! Wishing you strength, health, and a great fitness journey from the ARMOUR 24-7 GYM team! 🎂💪🎉");
                    $wishUrl = ! empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text={$wishText}" : route('app.members.show', $bMember->id);

                    $items[] = [
                        'id' => "bday_mem_{$bMember->id}_" . Carbon::today()->format('Ymd'),
                        'type' => 'birthday',
                        'category' => 'Birthdays',
                        'title' => "🎂 Birthday Today: {$bMember->full_name}",
                        'message' => "Celebrate {$bMember->first_name}'s birthday today with a warm WhatsApp wish!",
                        'time_ago' => 'Today',
                        'timestamp' => Carbon::now()->timestamp,
                        'link' => $wishUrl,
                        'action_label' => 'Wish on WhatsApp',
                        'is_external' => ! empty($cleanPhone),
                        'color' => 'purple',
                        'icon' => 'cake',
                    ];
                }
            }

            // E. New Uncontacted CRM Leads
            if ($user->hasPermission('crm.leads.view')) {
                $recentLeads = Lead::where('tenant_id', $tenant->id)
                    ->whereIn('status', ['new', 'NEW', 'hot', 'HOT'])
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($recentLeads as $lead) {
                    $items[] = [
                        'id' => "lead_{$lead->id}_{$lead->updated_at?->timestamp}",
                        'type' => 'lead',
                        'category' => 'Leads',
                        'title' => "👥 New Lead Inquiry: {$lead->name}",
                        'message' => "Source: " . ($lead->source ?: 'Walk-in') . " • Stage: " . ucfirst($lead->stage ?: 'New Inquiry'),
                        'time_ago' => $lead->created_at?->diffForHumans() ?? 'Recently',
                        'timestamp' => $lead->created_at?->timestamp ?? 0,
                        'link' => route('app.crm.leads'),
                        'action_label' => 'Contact Lead',
                        'color' => 'sky',
                        'icon' => 'user-plus',
                    ];
                }
            }
        }

        // Sort by timestamp descending
        usort($items, fn ($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));

        // Mark read flags
        $unreadCount = 0;
        $categoryCounts = [
            'all' => count($items),
            'whatsapp' => 0,
            'renewal' => 0,
            'payment' => 0,
            'birthday' => 0,
            'lead' => 0,
        ];

        foreach ($items as &$item) {
            $isRead = in_array($item['id'], $readIds, true);
            $item['is_read'] = $isRead;
            if (! $isRead) {
                $unreadCount++;
            }

            $type = $item['type'] ?? 'other';
            if (isset($categoryCounts[$type])) {
                $categoryCounts[$type]++;
            }
        }
        unset($item);

        return [
            'success' => true,
            'unread_count' => $unreadCount,
            'total_count' => count($items),
            'counts' => $categoryCounts,
            'notifications' => array_values($items),
        ];
    }

    /**
     * Mark a single notification item as read.
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $metadata = $user->metadata ?? [];
        if (! is_array($metadata)) {
            $metadata = [];
        }

        $read = $metadata['read_notifications'] ?? [];
        if (! is_array($read)) {
            $read = [];
        }

        if (! in_array($notificationId, $read, true)) {
            $read[] = $notificationId;
            // Keep recent 100 read IDs to avoid unbounded growth
            if (count($read) > 100) {
                $read = array_slice($read, -100);
            }
            $metadata['read_notifications'] = $read;
            $user->metadata = $metadata;
            $user->save();
        }
    }

    /**
     * Mark all provided notification IDs as read.
     */
    public function markAllAsRead(User $user, array $notificationIds): void
    {
        $metadata = $user->metadata ?? [];
        if (! is_array($metadata)) {
            $metadata = [];
        }

        $read = $metadata['read_notifications'] ?? [];
        if (! is_array($read)) {
            $read = [];
        }

        $merged = array_unique(array_merge($read, $notificationIds));
        if (count($merged) > 100) {
            $merged = array_slice($merged, -100);
        }

        $metadata['read_notifications'] = array_values($merged);
        $user->metadata = $metadata;
        $user->save();
    }
}
