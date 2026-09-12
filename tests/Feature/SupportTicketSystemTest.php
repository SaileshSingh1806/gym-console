<?php

namespace Tests\Feature;

use App\Mail\NewSupportTicketAdminNotification;
use App\Mail\SupportTicketReplyNotification;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketSystemTest extends TestCase
{
    use DatabaseTransactions;

    public function test_gym_owner_can_create_support_ticket_and_super_admin_can_manage_and_reply(): void
    {
        Mail::fake();
        Storage::fake('public');

        $tenantService = app(TenantService::class);
        $freePlan = Plan::where('slug', 'free-forever')->first();

        // 1. Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gymconsole.test'],
            [
                'name' => 'Platform Super Admin',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ]
        );

        // 2. Register a gym owner
        $registration = $tenantService->registerGym([
            'gym_name' => 'Iron Core Fitness',
            'email' => 'ironcore@testgym.com',
            'owner_name' => 'Iron Core Owner',
            'password' => 'password',
            'phone' => '9888888801',
        ], $freePlan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];

        // 3. Gym Owner accesses Support page
        $this->actingAs($owner)->get(route('app.support.index'))->assertOk();

        // 4. Gym Owner creates a new support ticket
        $attachment = UploadedFile::fake()->create('error_screenshot.png', 100, 'image/png');

        $response = $this->actingAs($owner)->post(route('app.support.store'), [
            'subject' => 'Need help setting up Biometrics device',
            'category' => 'technical',
            'priority' => 'high',
            'message' => 'Our Hikvision device is not syncing attendance automatically. Please advise.',
            'attachment' => $attachment,
        ]);

        $response->assertRedirect();

        // Assert database has ticket and reply
        $this->assertDatabaseHas('support_tickets', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'subject' => 'Need help setting up Biometrics device',
            'category' => 'technical',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $ticket = SupportTicket::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(1, $ticket->replies()->count());

        // Assert email was sent to Super Admin
        Mail::assertSent(NewSupportTicketAdminNotification::class, function ($mail) use ($superAdmin) {
            return $mail->hasTo($superAdmin->email);
        });

        // 5. Super Admin views support tickets in Admin Portal
        $this->actingAs($superAdmin)->get(route('admin.tickets.index'))->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('Iron Core Fitness')
            ->assertSee('Need help setting up Biometrics device');

        // Super Admin views ticket thread
        $this->actingAs($superAdmin)->get(route('admin.tickets.show', $ticket->id))->assertOk()
            ->assertSee('Our Hikvision device is not syncing attendance automatically');

        // 6. Super Admin replies to ticket
        $adminReply = $this->actingAs($superAdmin)->post(route('admin.tickets.reply', $ticket->id), [
            'message' => 'Please verify that the device IP is reachable and port 8000 is open in your local router.',
            'status' => 'answered',
        ]);

        $adminReply->assertRedirect()->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('answered', $ticket->status);
        $this->assertEquals(2, $ticket->replies()->count());

        // Assert email reply notification was sent to Gym Owner
        Mail::assertSent(SupportTicketReplyNotification::class, function ($mail) use ($owner) {
            return $mail->hasTo($owner->email);
        });

        // 7. Gym Owner views reply and sends follow-up
        $this->actingAs($owner)->get(route('app.support.show', $ticket->id))->assertOk()
            ->assertSee('Please verify that the device IP is reachable');

        $this->actingAs($owner)->post(route('app.support.reply', $ticket->id), [
            'message' => 'Port 8000 opened and now syncing properly! Thank you.',
        ])->assertRedirect()->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(3, $ticket->replies()->count());

        // 8. Gym owner marks ticket as closed
        $this->actingAs($owner)->post(route('app.support.close', $ticket->id))->assertRedirect()->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
    }
}
