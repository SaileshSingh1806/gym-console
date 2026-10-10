<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AppController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PublicController;
use Illuminate\Support\Facades\Route;

// Public Marketing Routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/features', [PublicController::class, 'features'])->name('features');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');

// Authentication & Onboarding Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/checkout', [AuthController::class, 'showCheckout'])->name('auth.checkout');
    Route::post('/checkout', [AuthController::class, 'processCheckout'])->name('auth.checkout.process');
    Route::post('/admin/leave-impersonation', [AdminController::class, 'leaveImpersonation'])->name('admin.leave-impersonation');
    Route::post('/theme-settings/save', [AppController::class, 'saveThemeSettings'])->name('theme.save');
});

// Gym Application Routes (Scoped to Active Tenant & Active Subscription)
Route::prefix('app')->middleware(['auth', 'tenant', 'subscription.active'])->group(function () {
    Route::get('/dashboard', [AppController::class, 'dashboard'])->name('app.dashboard');
    Route::get('/portal', [AppController::class, 'memberPortal'])->name('app.portal');

    // 1. Members Management
    Route::middleware(['feature:members_management'])->group(function () {
        Route::get('/members', [AppController::class, 'members'])->name('app.members.index')->middleware('permission:members.view');
        Route::get('/members/create', [AppController::class, 'createMember'])->name('app.members.create')->middleware('permission:members.create');
        Route::get('/members/lookup-phone', [AppController::class, 'lookupMemberPhone'])->name('app.members.lookup-phone')->middleware('permission:members.create,members.view');
        Route::get('/members/{id}', [AppController::class, 'showMember'])->name('app.members.show')->middleware('permission:members.view');
        Route::get('/members/{id}/edit', [AppController::class, 'editMember'])->name('app.members.edit')->middleware('permission:members.edit');
        Route::post('/members', [AppController::class, 'storeMember'])->name('app.members.store')->middleware('permission:members.create');
        Route::post('/members/{id}', [AppController::class, 'updateMember'])->name('app.members.update')->middleware('permission:members.edit');
        Route::delete('/members/{id}', [AppController::class, 'deleteMember'])->name('app.members.delete')->middleware('permission:members.delete');
        Route::post('/members/{id}/collect-fee', [AppController::class, 'collectMemberFee'])->name('app.members.collect-fee')->middleware('permission:payments.record');
        Route::post('/members/{id}/freeze', [AppController::class, 'toggleFreezeMember'])->name('app.members.freeze')->middleware('permission:members.freeze');
        Route::post('/members/{id}/add-subscription', [AppController::class, 'addMemberSubscription'])->name('app.members.add-subscription')->middleware('permission:memberships.create');
        Route::post('/members/{id}/add-pt-package', [AppController::class, 'addPtPackage'])->name('app.members.add-pt-package')->middleware('permission:pt.create');
        Route::post('/members/{id}/enroll-class', [AppController::class, 'enrollMemberInClass'])->name('app.members.enroll-class')->middleware('permission:classes.book');
    });

    // Member Measurements (Available to Trainers, Staff, and Members)
    Route::middleware(['feature:members_management', 'permission:members.measurements,members.edit,members.view'])->post('/members/{id}/measurements', [AppController::class, 'storeMemberMeasurement'])->name('app.members.store-measurement');

    // 2. Memberships & Billing
    Route::middleware(['feature:memberships_billing'])->group(function () {
        Route::get('/memberships', [AppController::class, 'memberships'])->name('app.memberships.index')->middleware('permission:memberships.view,plans.view');
        Route::post('/membership-plans', [AppController::class, 'storeMembershipPlan'])->name('app.membership-plans.store')->middleware('permission:plans.create');
        Route::post('/membership-plans/{id}', [AppController::class, 'updateMembershipPlan'])->name('app.membership-plans.update')->middleware('permission:plans.edit');
        Route::delete('/membership-plans/{id}', [AppController::class, 'deleteMembershipPlan'])->name('app.membership-plans.delete')->middleware('permission:plans.delete');
    });

    // 3. Payments & POS
    Route::middleware(['feature:payments_pos'])->group(function () {
        Route::get('/payments', [AppController::class, 'payments'])->name('app.payments.index')->middleware('permission:payments.view');
        Route::post('/payments', [AppController::class, 'storePayment'])->name('app.payments.store')->middleware('permission:payments.record');
        Route::post('/payments/{id}/reverse', [AppController::class, 'reversePayment'])->name('app.payments.reverse')->middleware('permission:payments.refund');
        Route::post('/payments/{id}/send-email', [AppController::class, 'sendPaymentReceipt'])->name('app.payments.send-email')->middleware('permission:payments.view,payments.record');
    });
    Route::middleware(['feature:payments_pos', 'permission:payments.invoice_view,payments.view'])->get('/invoices/{id}', [AppController::class, 'showInvoice'])->name('app.invoices.show');

    // 4. Attendance
    Route::middleware('feature:attendance_checkin')->group(function () {
        Route::get('/attendance', [AppController::class, 'attendance'])->name('app.attendance.index')->middleware('permission:attendance.view');
        Route::post('/attendance', [AppController::class, 'storeCheckin'])->name('app.attendance.store')->middleware('permission:attendance.checkin');
        Route::post('/attendance/{id}/checkout', [AppController::class, 'checkoutAttendance'])->name('app.attendance.checkout')->whereNumber('id')->middleware('permission:attendance.checkin');
    });

    // 5. Personal Training & PT Plans & Sessions
    Route::middleware('feature:personal_training')->group(function () {
        Route::get('/personal-training', [AppController::class, 'personalTraining'])->name('app.pt.index')->middleware('permission:pt.view');
        Route::post('/pt-plans', [AppController::class, 'storePtPlan'])->name('app.pt-plans.store')->middleware('permission:pt.plans.manage');
        Route::post('/pt-plans/{id}', [AppController::class, 'updatePtPlan'])->name('app.pt-plans.update')->middleware('permission:pt.plans.manage');
        Route::post('/pt-plans/{id}/toggle', [AppController::class, 'togglePtPlan'])->name('app.pt-plans.toggle')->middleware('permission:pt.plans.manage');
        Route::delete('/pt-plans/{id}', [AppController::class, 'deletePtPlan'])->name('app.pt-plans.delete')->middleware('permission:pt.plans.manage');
        Route::post('/pt-packages', [AppController::class, 'assignPtPackage'])->name('app.pt-packages.store')->middleware('permission:pt.create');
        Route::post('/pt-packages/{id}/log-session', [AppController::class, 'logPtSession'])->name('app.pt-packages.log-session')->middleware('permission:pt.sessions_manage');
        Route::post('/pt-packages/{id}/cancel', [AppController::class, 'cancelPtPackage'])->name('app.pt-packages.cancel')->middleware('permission:pt.delete');
        Route::post('/pt-sessions', [AppController::class, 'storePtSession'])->name('app.pt-sessions.store')->middleware('permission:pt.sessions_manage');
        Route::post('/pt-sessions/{id}/complete', [AppController::class, 'completePtSession'])->name('app.pt-sessions.complete')->middleware('permission:pt.sessions_manage');
        Route::post('/pt-sessions/{id}/status', [AppController::class, 'updatePtSessionStatus'])->name('app.pt-sessions.status')->middleware('permission:pt.sessions_manage');
    });

    // 6. Trainers
    Route::middleware(['feature:trainers_management'])->group(function () {
        Route::get('/trainers', [AppController::class, 'trainers'])->name('app.trainers.index')->middleware('permission:trainers.view');
        Route::post('/trainers', [AppController::class, 'storeTrainer'])->name('app.trainers.store')->middleware('permission:trainers.create');
        Route::post('/trainers/{id}', [AppController::class, 'updateTrainer'])->name('app.trainers.update')->middleware('permission:trainers.edit');
        Route::delete('/trainers/{id}', [AppController::class, 'deleteTrainer'])->name('app.trainers.delete')->middleware('permission:trainers.delete');
        Route::post('/trainers/{id}/assign-members', [AppController::class, 'assignTrainerMembers'])->name('app.trainers.assign-members')->middleware('permission:trainers.assign,trainers.edit');
    });

    // 7. Group Classes
    Route::middleware('feature:group_classes')->group(function () {
        Route::get('/classes', [AppController::class, 'classes'])->name('app.classes.index')->middleware('permission:classes.view');
        Route::post('/classes', [AppController::class, 'storeClass'])->name('app.classes.store')->middleware('permission:classes.create');
        Route::post('/classes/{id}', [AppController::class, 'updateClass'])->name('app.classes.update')->middleware('permission:classes.edit');
        Route::delete('/classes/{id}', [AppController::class, 'deleteClass'])->name('app.classes.delete')->middleware('permission:classes.delete');
        Route::post('/classes/schedules/{id}/book', [AppController::class, 'bookClassSchedule'])->name('app.classes.book')->middleware('permission:classes.book');
        Route::post('/classes/bookings/{id}/status', [AppController::class, 'updateClassBookingStatus'])->name('app.classes.booking-status')->middleware('permission:classes.book,classes.edit');
        Route::delete('/classes/bookings/{id}', [AppController::class, 'deleteClassBooking'])->name('app.classes.booking.delete')->middleware('permission:classes.book,classes.delete');
        Route::post('/classes/bookings/{id}/delete', [AppController::class, 'deleteClassBooking'])->name('app.classes.booking.delete.post')->middleware('permission:classes.book,classes.delete');
    });

    // 8. Workouts
    Route::middleware('feature:workout_plans')->group(function () {
        Route::get('/workouts', [AppController::class, 'workouts'])->name('app.workouts.index')->middleware('permission:workouts.view');
        Route::post('/workouts', [AppController::class, 'storeWorkout'])->name('app.workouts.store')->middleware('permission:workouts.create,workouts.edit');
        Route::delete('/workouts/{id}', [AppController::class, 'deleteWorkout'])->name('app.workouts.delete')->middleware('permission:workouts.delete');
    });

    // 9. Diets & Nutrition
    Route::middleware('feature:diet_nutrition')->group(function () {
        Route::get('/diets', [AppController::class, 'diets'])->name('app.diets.index')->middleware('permission:diets.view');
        Route::post('/diets', [AppController::class, 'storeDiet'])->name('app.diets.store')->middleware('permission:diets.create');
        Route::post('/diets/generate-ai', [AppController::class, 'generateAiDiet'])->name('app.diets.generate-ai')->middleware('permission:diets.create');
        Route::post('/diets/seed-starter', [AppController::class, 'seedStarterDiets'])->name('app.diets.seed')->middleware('permission:diets.create');
        Route::post('/diets/{id}', [AppController::class, 'updateDiet'])->name('app.diets.update')->whereNumber('id')->middleware('permission:diets.edit');
        Route::delete('/diets/{id}', [AppController::class, 'deleteDiet'])->name('app.diets.delete')->whereNumber('id')->middleware('permission:diets.delete');
    });

    // 10. Services & Bookings
    Route::middleware('feature:gym_services')->group(function () {
        Route::get('/services', [AppController::class, 'services'])->name('app.services.index')->middleware('permission:services.view');
        Route::post('/services', [AppController::class, 'storeService'])->name('app.services.store')->middleware('permission:services.create');
        Route::post('/services/{id}', [AppController::class, 'updateService'])->name('app.services.update')->whereNumber('id')->middleware('permission:services.edit');
        Route::post('/services/{id}/toggle-visibility', [AppController::class, 'toggleServiceVisibility'])->name('app.services.toggle-visibility')->whereNumber('id')->middleware('permission:services.edit');
        Route::delete('/services/{id}', [AppController::class, 'deleteService'])->name('app.services.delete')->whereNumber('id')->middleware('permission:services.delete');
        Route::post('/services/bookings', [AppController::class, 'storeServiceBooking'])->name('app.services.bookings.store')->middleware('permission:services.book');
        Route::post('/services/bookings/{id}/deduct', [AppController::class, 'deductServiceSession'])->name('app.services.bookings.deduct')->whereNumber('id')->middleware('permission:services.book');
        Route::post('/services/bookings/{id}/status', [AppController::class, 'updateBookingStatus'])->name('app.services.bookings.status')->whereNumber('id')->middleware('permission:services.book');
        Route::delete('/services/bookings/{id}', [AppController::class, 'deleteServiceBooking'])->name('app.services.bookings.delete')->whereNumber('id')->middleware('permission:services.book,services.delete');
    });

    // 11. CRM & Leads Suite
    Route::middleware(['feature:crm_leads'])->group(function () {
        Route::get('/crm', [AppController::class, 'crmDashboard'])->name('app.crm.index')->middleware('permission:crm.view');
        Route::get('/crm/dashboard', [AppController::class, 'crmDashboard'])->name('app.crm.dashboard')->middleware('permission:crm.view');
        Route::get('/crm/leads', [AppController::class, 'leads'])->name('app.crm.leads')->middleware('permission:crm.leads.view,crm.view');
        Route::get('/crm/leads/create', [AppController::class, 'createLead'])->name('app.crm.leads.create')->middleware('permission:crm.leads.create');
        Route::get('/crm/leads/{id}/edit', [AppController::class, 'editLead'])->name('app.crm.leads.edit')->whereNumber('id')->middleware('permission:crm.leads.edit');
        Route::get('/leads', [AppController::class, 'leads'])->name('app.leads.index')->middleware('permission:crm.leads.view,crm.view');
        Route::get('/leads/create', [AppController::class, 'createLead'])->name('app.leads.create')->middleware('permission:crm.leads.create');
        Route::get('/leads/{id}/edit', [AppController::class, 'editLead'])->name('app.leads.edit')->whereNumber('id')->middleware('permission:crm.leads.edit');
        Route::post('/leads', [AppController::class, 'storeLead'])->name('app.leads.store')->middleware('permission:crm.leads.create');
        Route::post('/leads/{id}', [AppController::class, 'updateLead'])->name('app.leads.update')->whereNumber('id')->middleware('permission:crm.leads.edit');
        Route::post('/leads/{id}/stage', [AppController::class, 'updateLeadStage'])->name('app.leads.stage')->whereNumber('id')->middleware('permission:crm.leads.edit');
        Route::delete('/leads/{id}', [AppController::class, 'deleteLead'])->name('app.leads.delete')->whereNumber('id')->middleware('permission:crm.leads.delete');

        Route::get('/crm/trials', [AppController::class, 'crmTrials'])->name('app.crm.trials')->middleware('permission:crm.trials,crm.view');
        Route::get('/trials', [AppController::class, 'crmTrials'])->name('app.trials.index')->middleware('permission:crm.trials,crm.view');
        Route::post('/crm/trials', [AppController::class, 'storeTrial'])->name('app.crm.trials.store')->middleware('permission:crm.trials');
        Route::post('/crm/trials/{id}/status', [AppController::class, 'updateTrialStatus'])->name('app.crm.trials.status')->whereNumber('id')->middleware('permission:crm.trials');
        Route::delete('/crm/trials/{id}', [AppController::class, 'deleteTrial'])->name('app.crm.trials.delete')->whereNumber('id')->middleware('permission:crm.trials');

        Route::get('/crm/enquiries', [AppController::class, 'crmEnquiries'])->name('app.crm.enquiries')->middleware('permission:crm.leads.view,crm.view');
        Route::post('/crm/enquiries', [AppController::class, 'storeEnquiry'])->name('app.crm.enquiries.store')->middleware('permission:crm.leads.create');
        Route::get('/crm/conversions', [AppController::class, 'crmConversions'])->name('app.crm.conversions')->middleware('permission:crm.reports,crm.view');
        Route::get('/crm/reports', [AppController::class, 'crmReports'])->name('app.crm.reports')->middleware('permission:crm.reports,crm.view');
    });

    // 12. Report & Finance
    Route::middleware(['feature:reports_finance'])->group(function () {
        Route::get('/finance/member-report', [AppController::class, 'expenseReport'])->name('app.finance.member-report')->middleware('permission:reports.member_report');
        Route::get('/finance/member-report/pdf', [AppController::class, 'memberReportPdf'])->name('app.finance.member-report.pdf')->middleware('permission:reports.member_report');
        Route::get('/finance/expense-report', [AppController::class, 'expenseReport'])->name('app.finance.expense-report')->middleware('permission:reports.member_report,expenses.view');
        Route::get('/finance/expense-report/pdf', [AppController::class, 'memberReportPdf'])->name('app.finance.expense-report.pdf')->middleware('permission:reports.member_report,expenses.view');
        Route::get('/reports', [AppController::class, 'expenseReport'])->name('app.reports.index')->middleware('permission:reports.member_report,expenses.view');
        Route::get('/expenses', [AppController::class, 'expenses'])->name('app.expenses.index')->middleware('permission:expenses.view');
        Route::post('/expenses', [AppController::class, 'storeExpense'])->name('app.expenses.store')->middleware('permission:expenses.create');
        Route::delete('/expenses/{id}', [AppController::class, 'deleteExpense'])->name('app.expenses.delete')->whereNumber('id')->middleware('permission:expenses.delete');
        Route::post('/expenses/categories', [AppController::class, 'storeExpenseCategory'])->name('app.expenses.categories.store')->middleware('permission:expenses.create');

        Route::get('/finance/balance-sheet', [AppController::class, 'balanceSheet'])->name('app.finance.balance-sheet')->middleware('permission:reports.balance_sheet');
        Route::get('/finance/balance-sheet/pdf', [AppController::class, 'balanceSheetPdf'])->name('app.finance.balance-sheet.pdf')->middleware('permission:reports.balance_sheet');
        Route::get('/balance-sheet', [AppController::class, 'balanceSheet'])->name('app.balance-sheet.index')->middleware('permission:reports.balance_sheet');
    });

    // 13 & 14. Inventory & Equipment Maintenance
    Route::middleware(['feature:inventory_stock,equipment_maintenance'])->group(function () {
        Route::get('/inventory', [AppController::class, 'inventory'])->name('app.inventory.index')->middleware('permission:inventory.view,equipment.maintenance');
    });

    Route::middleware(['feature:inventory_stock'])->group(function () {
        Route::post('/inventory/items', [AppController::class, 'storeInventoryItem'])->name('app.inventory.items.store')->middleware('permission:inventory.create');
        Route::post('/inventory/items/{id}', [AppController::class, 'updateInventoryItem'])->name('app.inventory.items.update')->whereNumber('id')->middleware('permission:inventory.edit');
        Route::post('/inventory/items/{id}/adjust', [AppController::class, 'adjustInventoryStock'])->name('app.inventory.items.adjust')->whereNumber('id')->middleware('permission:inventory.edit');
        Route::delete('/inventory/items/{id}', [AppController::class, 'deleteInventoryItem'])->name('app.inventory.items.delete')->whereNumber('id')->middleware('permission:inventory.delete');
    });

    Route::middleware(['feature:equipment_maintenance'])->group(function () {
        Route::post('/inventory/equipment', [AppController::class, 'storeEquipment'])->name('app.inventory.equipment.store')->middleware('permission:equipment.maintenance');
        Route::post('/inventory/equipment/{id}', [AppController::class, 'updateEquipment'])->name('app.inventory.equipment.update')->whereNumber('id')->middleware('permission:equipment.maintenance');
        Route::post('/inventory/equipment/{id}/maintenance', [AppController::class, 'recordEquipmentMaintenance'])->name('app.inventory.equipment.maintenance.store')->whereNumber('id')->middleware('permission:equipment.maintenance');
        Route::delete('/inventory/equipment/{id}', [AppController::class, 'deleteEquipment'])->name('app.inventory.equipment.delete')->whereNumber('id')->middleware('permission:equipment.maintenance');
    });

    // 15. Biometric & IoT Devices
    Route::middleware(['feature:hikvision_iot'])->group(function () {
        Route::get('/devices', [AppController::class, 'devices'])->name('app.devices.index')->middleware('permission:devices.view');
        Route::post('/devices', [AppController::class, 'storeDevice'])->name('app.devices.store')->middleware('permission:devices.manage');
        Route::post('/devices/{id}/test', [AppController::class, 'testDevice'])->name('app.devices.test')->whereNumber('id')->middleware('permission:devices.manage');
        Route::delete('/devices/{id}', [AppController::class, 'deleteDevice'])->name('app.devices.delete')->whereNumber('id')->middleware('permission:devices.manage');
    });

    // 16. Staff Management
    Route::middleware(['feature:staff_roles'])->group(function () {
        Route::get('/staff', [AppController::class, 'staff'])->name('app.staff.index')->middleware('permission:staff.view');
        Route::post('/staff', [AppController::class, 'storeStaff'])->name('app.staff.store')->middleware('permission:staff.create');
        Route::post('/staff/{id}', [AppController::class, 'updateStaff'])->name('app.staff.update')->middleware('permission:staff.edit');
        Route::delete('/staff/{id}', [AppController::class, 'deleteStaff'])->name('app.staff.delete')->middleware('permission:staff.delete');
        Route::post('/staff/{id}/toggle-status', [AppController::class, 'toggleStaffStatus'])->name('app.staff.toggle-status')->middleware('permission:staff.edit');
        Route::post('/staff/{id}/reset-password', [AppController::class, 'resetStaffPassword'])->name('app.staff.reset-password')->middleware('permission:staff.edit');
    });

    // 17. Roles & Permissions Management (Owner Only)
    Route::middleware(['feature:staff_roles', 'role:super_admin,gym_owner'])->group(function () {
        Route::get('/roles', [AppController::class, 'roles'])->name('app.roles.index');
        Route::post('/roles', [AppController::class, 'storeRole'])->name('app.roles.store');
        Route::post('/roles/matrix', [AppController::class, 'updatePermissionMatrix'])->name('app.roles.matrix.update');
        Route::post('/roles/{id}', [AppController::class, 'updateRole'])->name('app.roles.update')->whereNumber('id');
        Route::delete('/roles/{id}', [AppController::class, 'deleteRole'])->name('app.roles.delete')->whereNumber('id');
    });

    // SaaS Subscription (Always available to view plan / upgrade)
    Route::get('/subscription', [AppController::class, 'subscription'])->name('app.subscription.index');
    Route::post('/subscription/upgrade', [AppController::class, 'upgradePlan'])->name('app.subscription.upgrade');
    Route::post('/coupon/validate', [AppController::class, 'validateCoupon'])->name('app.coupon.validate');

    // Help & Support Tickets
    Route::get('/support', [AppController::class, 'supportTickets'])->name('app.support.index');
    Route::post('/support', [AppController::class, 'storeSupportTicket'])->name('app.support.store');
    Route::get('/support/{id}', [AppController::class, 'showSupportTicket'])->name('app.support.show')->whereNumber('id');
    Route::post('/support/{id}/reply', [AppController::class, 'replySupportTicket'])->name('app.support.reply')->whereNumber('id');
    Route::post('/support/{id}/close', [AppController::class, 'closeSupportTicket'])->name('app.support.close')->whereNumber('id');

    // Settings (Owner Only)
    Route::middleware('role:super_admin,gym_owner')->group(function () {
        Route::get('/settings', [AppController::class, 'settings'])->name('app.settings.index');
        Route::post('/settings', [AppController::class, 'updateSettings'])->name('app.settings.update');
        Route::post('/settings/email/test', [AppController::class, 'sendGymTestEmail'])->name('app.settings.email.test');
    });

    // Branches (Owner Only)
    Route::middleware('role:super_admin,gym_owner')->group(function () {
        Route::get('/branches', [AppController::class, 'branches'])->name('app.branches.index');
        Route::post('/branches', [AppController::class, 'storeBranch'])->name('app.branches.store');
        Route::post('/branches/{id}', [AppController::class, 'updateBranch'])->name('app.branches.update')->whereNumber('id');
        Route::delete('/branches/{id}', [AppController::class, 'deleteBranch'])->name('app.branches.delete')->whereNumber('id');
    });
    Route::post('/branches/{id}/switch', [AppController::class, 'switchBranch'])->name('app.branches.switch')->whereNumber('id');
});

// SaaS Super Admin Routes (Fully Editable Platform Administration)
Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Support Tickets (Super Admin Helpdesk)
    Route::get('/tickets', [AdminController::class, 'supportTickets'])->name('admin.tickets.index');
    Route::get('/tickets/{id}', [AdminController::class, 'showSupportTicket'])->name('admin.tickets.show')->whereNumber('id');
    Route::post('/tickets/{id}/reply', [AdminController::class, 'replySupportTicket'])->name('admin.tickets.reply')->whereNumber('id');
    Route::post('/tickets/{id}/status', [AdminController::class, 'updateTicketStatus'])->name('admin.tickets.status')->whereNumber('id');
    Route::delete('/tickets/{id}', [AdminController::class, 'deleteSupportTicket'])->name('admin.tickets.delete')->whereNumber('id');

    // Gyms
    Route::get('/gyms', [AdminController::class, 'gyms'])->name('admin.gyms');
    Route::post('/gyms', [AdminController::class, 'storeGym'])->name('admin.gyms.store');
    Route::post('/gyms/{id}', [AdminController::class, 'updateGym'])->name('admin.gyms.update');
    Route::delete('/gyms/{id}', [AdminController::class, 'deleteGym'])->name('admin.gyms.delete');
    Route::post('/gyms/{id}/impersonate', [AdminController::class, 'impersonateGym'])->name('admin.gyms.impersonate');

    // SaaS Plans & Features
    Route::get('/plans', [AdminController::class, 'plans'])->name('admin.plans');
    Route::post('/plans', [AdminController::class, 'storePlan'])->name('admin.plans.store');
    Route::post('/plans/{id}', [AdminController::class, 'updatePlan'])->name('admin.plans.update');
    Route::delete('/plans/{id}', [AdminController::class, 'deletePlan'])->name('admin.plans.delete');
    Route::post('/features', [AdminController::class, 'storeFeature'])->name('admin.features.store');

    // Promo Coupons & Discounts
    Route::get('/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('admin.coupons.store');
    Route::post('/coupons/{id}', [AdminController::class, 'updateCoupon'])->name('admin.coupons.update');
    Route::delete('/coupons/{id}', [AdminController::class, 'deleteCoupon'])->name('admin.coupons.delete');
    Route::post('/coupons/{id}/toggle', [AdminController::class, 'toggleCouponStatus'])->name('admin.coupons.toggle');

    // Subscriptions & Manual Payments
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('admin.subscriptions');
    Route::post('/subscriptions/manual-payment', [AdminController::class, 'recordManualPayment'])->name('admin.subscriptions.manual-payment');
    Route::post('/subscriptions/{id}', [AdminController::class, 'updateSubscription'])->name('admin.subscriptions.update')->whereNumber('id');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Activity Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::post('/logs/clear', [AdminController::class, 'clearLogs'])->name('admin.logs.clear');

    // Global Platform Settings (General, Logo/Favicon, Email/SMTP, Razorpay Payment, Site SEO, AI Gemini)
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/general', [AdminController::class, 'updateGeneralSettings'])->name('admin.settings.general');
    Route::post('/settings/email', [AdminController::class, 'updateEmailSettings'])->name('admin.settings.email');
    Route::post('/settings/email/test', [AdminController::class, 'sendTestEmail'])->name('admin.settings.email.test');
    Route::post('/settings/payment', [AdminController::class, 'updatePaymentSettings'])->name('admin.settings.payment');
    Route::post('/settings/seo', [AdminController::class, 'updateSeoSettings'])->name('admin.settings.seo');
    Route::post('/settings/ai', [AdminController::class, 'updateAiSettings'])->name('admin.settings.ai');
    Route::post('/settings/gemini/test', [AdminController::class, 'testAdminGeminiConnection'])->name('admin.settings.gemini.test');
    Route::post('/settings/password', [AdminController::class, 'updatePassword'])->name('admin.settings.password');
});
