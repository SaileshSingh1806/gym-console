<?php

namespace App\Services;

use App\Mail\NewSupportTicketAdminNotification;
use App\Mail\SupportTicketReplyNotification;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SupportTicketService
{
    /**
     * Create a new support ticket from a gym owner / tenant user.
     */
    public function createTicket(Tenant $tenant, User $user, array $data, ?UploadedFile $attachment = null): SupportTicket
    {
        $attachmentPath = null;
        $attachmentName = null;

        if ($attachment && $attachment->isValid()) {
            $attachmentName = $attachment->getClientOriginalName();
            $attachmentPath = $attachment->store('support_attachments', 'public');
        }

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'subject' => trim($data['subject']),
            'category' => $data['category'] ?? 'general',
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by_user_id' => $user->id,
        ]);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin_reply' => false,
            'message' => trim($data['message']),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        ActivityLog::log('support_ticket_created', "Created support ticket #{$ticket->ticket_number}: {$ticket->subject}", $ticket);

        // Safely send email notification to Super Admin
        $this->notifySuperAdminsOnNewTicket($ticket, trim($data['message']));

        return $ticket;
    }

    /**
     * Post a reply to an existing support ticket.
     */
    public function replyTicket(
        SupportTicket $ticket,
        User $user,
        string $message,
        ?UploadedFile $attachment = null,
        bool $isAdmin = false,
        ?string $newStatus = null
    ): SupportTicketReply {
        $attachmentPath = null;
        $attachmentName = null;

        if ($attachment && $attachment->isValid()) {
            $attachmentName = $attachment->getClientOriginalName();
            $attachmentPath = $attachment->store('support_attachments', 'public');
        }

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin_reply' => $isAdmin,
            'message' => trim($message),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $statusToSet = $newStatus;
        if (! $statusToSet) {
            if ($isAdmin) {
                $statusToSet = 'answered';
            } else {
                // If user replies to a resolved/answered ticket, reopen it
                $statusToSet = in_array($ticket->status, ['resolved', 'closed', 'answered']) ? 'open' : $ticket->status;
            }
        }

        $ticket->update([
            'status' => $statusToSet,
            'last_reply_at' => now(),
            'last_reply_by_user_id' => $user->id,
            'resolved_at' => in_array($statusToSet, ['resolved', 'closed']) ? now() : ($statusToSet === 'open' ? null : $ticket->resolved_at),
        ]);

        ActivityLog::log('support_ticket_replied', "Replied to ticket #{$ticket->ticket_number}", $ticket);

        // Send email notification safely
        $this->notifyOnReply($ticket, $reply, $isAdmin);

        return $reply;
    }

    /**
     * Notify Super Admins when a new ticket is opened.
     */
    public function notifySuperAdminsOnNewTicket(SupportTicket $ticket, string $message): void
    {
        try {
            // Find super admin emails or platform support email
            $adminEmails = User::where('role', 'super_admin')->pluck('email')->filter()->all();
            $supportEmail = Setting::getGlobal('support_email');

            if ($supportEmail && ! in_array($supportEmail, $adminEmails)) {
                $adminEmails[] = $supportEmail;
            }

            if (empty($adminEmails)) {
                $adminEmails = [config('mail.from.address')];
            }

            foreach ($adminEmails as $email) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    AsyncMailService::dispatch($ticket->tenant_id, $email, new NewSupportTicketAdminNotification($ticket, $message));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch support ticket email to super admin: {$e->getMessage()}");
        }
    }

    /**
     * Notify user or super admin when a reply is posted.
     */
    public function notifyOnReply(SupportTicket $ticket, SupportTicketReply $reply, bool $isAdmin): void
    {
        try {
            if ($isAdmin) {
                // Notify ticket creator (gym owner)
                $creatorEmail = $ticket->user->email ?? null;
                if ($creatorEmail && filter_var($creatorEmail, FILTER_VALIDATE_EMAIL)) {
                    AsyncMailService::dispatch($ticket->tenant_id, $creatorEmail, new SupportTicketReplyNotification($ticket, $reply, true));
                }
            } else {
                // Notify super admins
                $adminEmails = User::where('role', 'super_admin')->pluck('email')->filter()->all();
                foreach ($adminEmails as $email) {
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        AsyncMailService::dispatch($ticket->tenant_id, $email, new SupportTicketReplyNotification($ticket, $reply, false));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch ticket reply notification email: {$e->getMessage()}");
        }
    }
}
