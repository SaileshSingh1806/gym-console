<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GymEquipment;
use App\Models\InventoryItem;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventoryAndEquipmentTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::firstOrCreate(
            ['slug' => 'pro'],
            ['name' => 'Pro Plan', 'price_monthly' => 1999, 'price_yearly' => 19990, 'trial_days' => 14, 'member_limit' => 500, 'branch_limit' => 2, 'staff_limit' => 10]
        );

        $tenantService = app(TenantService::class);
        $rand = Str::random(5);
        $reg = $tenantService->registerGym([
            'gym_name' => "Fitness Pro Club {$rand}",
            'email' => "manager_{$rand}@gym.com",
            'owner_name' => 'Gym Manager',
            'password' => 'password123',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ], $plan);

        $this->tenant = $reg['tenant'];
        $this->user = $reg['owner'];
        $this->branch = $reg['branch'];
    }

    public function test_inventory_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Inventory & Equipment Maintenance', false);
        $response->assertSee('Store Inventory & Stock', false);
        $response->assertSee('Gym Machines & AC Maintenance', false);
    }

    public function test_can_create_and_update_inventory_item(): void
    {
        $response = $this->actingAs($this->user)->post(route('app.inventory.items.store'), [
            'name' => 'Gold Standard 100% Whey 2kg',
            'sku' => 'ON-WHEY-2KG',
            'category' => 'Supplements',
            'cost_price' => 4500.00,
            'selling_price' => 6200.00,
            'stock_quantity' => 15,
            'reorder_threshold' => 4,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertRedirect(route('app.inventory.index', ['tab' => 'inventory']));
        $this->assertDatabaseHas('inventory_items', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Gold Standard 100% Whey 2kg',
            'sku' => 'ON-WHEY-2KG',
            'stock_quantity' => 15,
        ]);

        $item = InventoryItem::where('tenant_id', $this->tenant->id)->where('sku', 'ON-WHEY-2KG')->first();

        // Update item
        $updateResponse = $this->actingAs($this->user)->post(route('app.inventory.items.update', $item->id), [
            'name' => 'Gold Standard 100% Whey 2kg (Double Rich Chocolate)',
            'sku' => 'ON-WHEY-2KG-DRC',
            'category' => 'Supplements',
            'cost_price' => 4600.00,
            'selling_price' => 6400.00,
            'reorder_threshold' => 5,
        ]);

        $updateResponse->assertRedirect(route('app.inventory.index', ['tab' => 'inventory']));
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'name' => 'Gold Standard 100% Whey 2kg (Double Rich Chocolate)',
            'sku' => 'ON-WHEY-2KG-DRC',
        ]);
    }

    public function test_can_adjust_inventory_stock(): void
    {
        $item = InventoryItem::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Creatine Monohydrate 250g',
            'sku' => 'CREATINE-250G',
            'category' => 'Supplements',
            'cost_price' => 800.00,
            'selling_price' => 1200.00,
            'stock_quantity' => 10,
            'reorder_threshold' => 3,
        ]);

        // Add 5 units
        $this->actingAs($this->user)->post(route('app.inventory.items.adjust', $item->id), [
            'type' => 'IN',
            'quantity' => 5,
            'notes' => 'Restock from warehouse',
        ]);

        $item->refresh();
        $this->assertEquals(15, $item->stock_quantity);

        // Deduct 3 units
        $this->actingAs($this->user)->post(route('app.inventory.items.adjust', $item->id), [
            'type' => 'OUT',
            'quantity' => 3,
            'notes' => 'Direct counter sale',
        ]);

        $item->refresh();
        $this->assertEquals(12, $item->stock_quantity);

        // Check inventory logs
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'type' => 'IN',
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'type' => 'OUT',
            'quantity' => 3,
        ]);
    }

    public function test_can_delete_inventory_item(): void
    {
        $item = InventoryItem::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Energy Bar 50g',
            'sku' => 'BAR-50G',
            'category' => 'Beverages & Energy',
            'cost_price' => 40.00,
            'selling_price' => 80.00,
            'stock_quantity' => 20,
            'reorder_threshold' => 5,
        ]);

        $response = $this->actingAs($this->user)->delete(route('app.inventory.items.delete', $item->id));

        $response->assertRedirect(route('app.inventory.index', ['tab' => 'inventory']));
        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_can_create_and_manage_gym_equipment_and_ac_units(): void
    {
        // 1. Create AC Unit
        $response = $this->actingAs($this->user)->post(route('app.inventory.equipment.store'), [
            'name' => 'Main Workout Floor AC #1 (Daikin 2-Ton)',
            'category' => 'AC & HVAC',
            'brand' => 'Daikin',
            'model_number' => 'FTKF60TV',
            'serial_number' => 'DAIKIN-849204',
            'location' => 'Main Workout Hall',
            'purchase_date' => '2026-01-10',
            'purchase_cost' => 58000.00,
            'maintenance_interval_days' => 90, // Quarterly
            'last_service_date' => '2026-06-01',
            'status' => 'OPERATIONAL',
            'vendor_name' => 'Daikin Official Care',
            'vendor_contact' => '+91 9876543210',
            'branch_id' => $this->branch->id,
        ]);

        $response->assertRedirect(route('app.inventory.index', ['tab' => 'equipment']));
        $this->assertDatabaseHas('gym_equipment', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Workout Floor AC #1 (Daikin 2-Ton)',
            'category' => 'AC & HVAC',
            'brand' => 'Daikin',
        ]);

        $ac = GymEquipment::where('tenant_id', $this->tenant->id)->where('name', 'Main Workout Floor AC #1 (Daikin 2-Ton)')->first();
        $this->assertNotNull($ac->next_service_date);

        // 2. Update AC Unit
        $updateResponse = $this->actingAs($this->user)->post(route('app.inventory.equipment.update', $ac->id), [
            'name' => 'Main Workout Floor AC #1 (Daikin 2-Ton Inverter)',
            'category' => 'AC & HVAC',
            'brand' => 'Daikin',
            'model_number' => 'FTKF60TV-INV',
            'serial_number' => 'DAIKIN-849204',
            'location' => 'Cardio Deck & Main Floor',
            'maintenance_interval_days' => 60, // Every 60 days
            'next_service_date' => Carbon::today()->addDays(20)->toDateString(),
            'status' => 'OPERATIONAL',
            'vendor_name' => 'Daikin Super Care',
            'vendor_contact' => '+91 9876543210',
        ]);

        $updateResponse->assertRedirect(route('app.inventory.index', ['tab' => 'equipment']));
        $this->assertDatabaseHas('gym_equipment', [
            'id' => $ac->id,
            'name' => 'Main Workout Floor AC #1 (Daikin 2-Ton Inverter)',
            'maintenance_interval_days' => 60,
        ]);

        // 3. Record Maintenance Service for AC
        $serviceDate = Carbon::today()->toDateString();
        $maintenanceResponse = $this->actingAs($this->user)->post(route('app.inventory.equipment.maintenance.store', $ac->id), [
            'service_date' => $serviceDate,
            'maintenance_type' => 'AC Filter Cleaning & Deep Wash',
            'technician_name' => 'Sanjay Technician',
            'technician_contact' => '+91 9123456780',
            'cost' => 1200.00,
            'status_after_service' => 'OPERATIONAL',
            'work_summary' => 'Deep pressure wash done, condenser cleaned, airflow normal.',
            'replaced_parts' => 'Air Filter Mesh',
            'auto_schedule_next' => 1,
        ]);

        $maintenanceResponse->assertRedirect(route('app.inventory.index', ['tab' => 'equipment']));
        $this->assertDatabaseHas('equipment_maintenance_logs', [
            'gym_equipment_id' => $ac->id,
            'maintenance_type' => 'AC Filter Cleaning & Deep Wash',
            'cost' => 1200.00,
            'technician_name' => 'Sanjay Technician',
        ]);

        $ac->refresh();
        $this->assertEquals($serviceDate, $ac->last_service_date->toDateString());
        // Next service date should be serviceDate + 60 days
        $this->assertEquals(Carbon::parse($serviceDate)->addDays(60)->toDateString(), $ac->next_service_date->toDateString());
        $this->assertEquals('OPERATIONAL', $ac->status);

        // 4. Delete equipment
        $deleteResponse = $this->actingAs($this->user)->delete(route('app.inventory.equipment.delete', $ac->id));
        $deleteResponse->assertRedirect(route('app.inventory.index', ['tab' => 'equipment']));
        $this->assertDatabaseMissing('gym_equipment', ['id' => $ac->id]);
    }
}
