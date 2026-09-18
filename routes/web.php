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

    // 1. Members Management
    Route::middleware(['feature:members_management', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant,staff,trainer'])->group(function () {
        Route::get('/members', [AppController::class, 'members'])->name('app.members.index');
        Route::get('/members/create', [AppController::class, 'createMember'])->name('app.members.create');
        Route::get('/members/lookup-phone', [AppController::class, 'lookupMemberPhone'])->name('app.members.lookup-phone');
        Route::get('/members/{id}', [AppController::class, 'showMember'])->name('app.members.show');
        Route::get('/members/{id}/edit', [AppController::class, 'editMember'])->name('app.members.edit');
        Route::post('/members', [AppController::class, 'storeMember'])->name('app.members.store');
        Route::post('/members/{id}', [AppController::class, 'updateMember'])->name('app.members.update');
        Route::delete('/members/{id}', [AppController::class, 'deleteMember'])->name('app.members.delete')->middleware('role:super_admin,gym_owner');
        Route::post('/members/{id}/collect-fee', [AppController::class, 'collectMemberFee'])->name('app.members.collect-fee');
        Route::post('/members/{id}/freeze', [AppController::class, 'toggleFreezeMember'])->name('app.members.freeze');
        Route::post('/members/{id}/add-subscription', [AppController::class, 'addMemberSubscription'])->name('app.members.add-subscription');
        Route::post('/members/{id}/add-pt-package', [AppController::class, 'addPtPackage'])->name('app.members.add-pt-package');
    });

    // Member Measurements (Available to Trainers, Staff, and Members)
    Route::middleware('feature:members_management')->post('/members/{id}/measurements', [AppController::class, 'storeMemberMeasurement'])->name('app.members.store-measurement');

    // 2. Memberships & Billing
    Route::middleware(['feature:memberships_billing', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant'])->group(function () {
        Route::get('/memberships', [AppController::class, 'memberships'])->name('app.memberships.index');
        Route::post('/membership-plans', [AppController::class, 'storeMembershipPlan'])->name('app.membership-plans.store');
        Route::post('/membership-plans/{id}', [AppController::class, 'updateMembershipPlan'])->name('app.membership-plans.update');
        Route::delete('/membership-plans/{id}', [AppController::class, 'deleteMembershipPlan'])->name('app.membership-plans.delete');
    });

    // 3. Payments & POS
    Route::middleware(['feature:payments_pos', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant'])->group(function () {
        Route::get('/payments', [AppController::class, 'payments'])->name('app.payments.index');
        Route::post('/payments', [AppController::class, 'storePayment'])->name('app.payments.store');
        Route::post('/payments/{id}/reverse', [AppController::class, 'reversePayment'])->name('app.payments.reverse')->middleware('role:super_admin,gym_owner,gym_manager');
        Route::post('/payments/{id}/send-email', [AppController::class, 'sendPaymentReceipt'])->name('app.payments.send-email');
    });
    Route::middleware('feature:payments_pos')->get('/invoices/{id}', [AppController::class, 'showInvoice'])->name('app.invoices.show');

    // 4. Attendance
    Route::middleware('feature:attendance_checkin')->group(function () {
        Route::get('/attendance', [AppController::class, 'attendance'])->name('app.attendance.index');
        Route::post('/attendance', [AppController::class, 'storeCheckin'])->name('app.attendance.store');
        Route::post('/attendance/{id}/checkout', [AppController::class, 'checkoutAttendance'])->name('app.attendance.checkout')->whereNumber('id');
    });

    // 5. Personal Training & PT Plans & Sessions
    Route::middleware('feature:personal_training')->group(function () {
        Route::get('/personal-training', [AppController::class, 'personalTraining'])->name('app.pt.index');
        Route::post('/pt-plans', [AppController::class, 'storePtPlan'])->name('app.pt-plans.store');
        Route::post('/pt-plans/{id}', [AppController::class, 'updatePtPlan'])->name('app.pt-plans.update');
        Route::post('/pt-plans/{id}/toggle', [AppController::class, 'togglePtPlan'])->name('app.pt-plans.toggle');
        Route::delete('/pt-plans/{id}', [AppController::class, 'deletePtPlan'])->name('app.pt-plans.delete');
        Route::post('/pt-packages', [AppController::class, 'assignPtPackage'])->name('app.pt-packages.store');
        Route::post('/pt-packages/{id}/log-session', [AppController::class, 'logPtSession'])->name('app.pt-packages.log-session');
        Route::post('/pt-packages/{id}/cancel', [AppController::class, 'cancelPtPackage'])->name('app.pt-packages.cancel');
        Route::post('/pt-sessions', [AppController::class, 'storePtSession'])->name('app.pt-sessions.store');
        Route::post('/pt-sessions/{id}/complete', [AppController::class, 'completePtSession'])->name('app.pt-sessions.complete');
        Route::post('/pt-sessions/{id}/status', [AppController::class, 'updatePtSessionStatus'])->name('app.pt-sessions.status');
    });

    // 6. Trainers
    Route::middleware(['feature:trainers_management', 'role:super_admin,gym_owner,gym_manager'])->group(function () {
        Route::get('/trainers', [AppController::class, 'trainers'])->name('app.trainers.index');
        Route::post('/trainers', [AppController::class, 'storeTrainer'])->name('app.trainers.store');
        Route::post('/trainers/{id}', [AppController::class, 'updateTrainer'])->name('app.trainers.update');
        Route::delete('/trainers/{id}', [AppController::class, 'deleteTrainer'])->name('app.trainers.delete');
        Route::post('/trainers/{id}/assign-members', [AppController::class, 'assignTrainerMembers'])->name('app.trainers.assign-members');
    });

    // 7. Group Classes
    Route::middleware('feature:group_classes')->group(function () {
        Route::get('/classes', [AppController::class, 'classes'])->name('app.classes.index');
        Route::post('/classes', [AppController::class, 'storeClass'])->name('app.classes.store');
        Route::post('/classes/{id}', [AppController::class, 'updateClass'])->name('app.classes.update');
        Route::delete('/classes/{id}', [AppController::class, 'deleteClass'])->name('app.classes.delete');
        Route::post('/classes/schedules/{id}/book', [AppController::class, 'bookClassSchedule'])->name('app.classes.book');
        Route::post('/classes/bookings/{id}/status', [AppController::class, 'updateClassBookingStatus'])->name('app.classes.booking-status');
    });

    // 8. Workouts
    Route::middleware('feature:workout_plans')->group(function () {
        Route::get('/workouts', [AppController::class, 'workouts'])->name('app.workouts.index');
        Route::post('/workouts', [AppController::class, 'storeWorkout'])->name('app.workouts.store');
        Route::delete('/workouts/{id}', [AppController::class, 'deleteWorkout'])->name('app.workouts.delete');
    });

    // 9. Diets & Nutrition
    Route::middleware('feature:diet_nutrition')->group(function () {
        Route::get('/diets', [AppController::class, 'diets'])->name('app.diets.index');
        Route::post('/diets', [AppController::class, 'storeDiet'])->name('app.diets.store');
        Route::post('/diets/generate-ai', [AppController::class, 'generateAiDiet'])->name('app.diets.generate-ai');
        Route::post('/diets/seed-starter', [AppController::class, 'seedStarterDiets'])->name('app.diets.seed');
        Route::post('/diets/{id}', [AppController::class, 'updateDiet'])->name('app.diets.update')->whereNumber('id');
        Route::delete('/diets/{id}', [AppController::class, 'deleteDiet'])->name('app.diets.delete')->whereNumber('id');
    });

    // 10. Services & Bookings
    Route::middleware('feature:gym_services')->group(function () {
        Route::get('/services', [AppController::class, 'services'])->name('app.services.index');
        Route::post('/services', [AppController::class, 'storeService'])->name('app.services.store');
        Route::post('/services/{id}', [AppController::class, 'updateService'])->name('app.services.update')->whereNumber('id');
        Route::post('/services/{id}/toggle-visibility', [AppController::class, 'toggleServiceVisibility'])->name('app.services.toggle-visibility')->whereNumber('id');
        Route::delete('/services/{id}', [AppController::class, 'deleteService'])->name('app.services.delete')->whereNumber('id');
        Route::post('/services/bookings', [AppController::class, 'storeServiceBooking'])->name('app.services.bookings.store');
        Route::post('/services/bookings/{id}/deduct', [AppController::class, 'deductServiceSession'])->name('app.services.bookings.deduct')->whereNumber('id');
        Route::post('/services/bookings/{id}/status', [AppController::class, 'updateBookingStatus'])->name('app.services.bookings.status')->whereNumber('id');
        Route::delete('/services/bookings/{id}', [AppController::class, 'deleteServiceBooking'])->name('app.services.bookings.delete')->whereNumber('id');
    });

    // 11. CRM & Leads Suite
    Route::middleware(['feature:crm_leads', 'role:super_admin,gym_owner,gym_manager,receptionist,staff'])->group(function () {
        Route::get('/crm', [AppController::class, 'crmDashboard'])->name('app.crm.index');
        Route::get('/crm/dashboard', [AppController::class, 'crmDashboard'])->name('app.crm.dashboard');
        Route::get('/crm/leads', [AppController::class, 'leads'])->name('app.crm.leads');
        Route::get('/crm/leads/create', [AppController::class, 'createLead'])->name('app.crm.leads.create');
        Route::get('/crm/leads/{id}/edit', [AppController::class, 'editLead'])->name('app.crm.leads.edit')->whereNumber('id');
        Route::get('/leads', [AppController::class, 'leads'])->name('app.leads.index');
        Route::get('/leads/create', [AppController::class, 'createLead'])->name('app.leads.create');
        Route::get('/leads/{id}/edit', [AppController::class, 'editLead'])->name('app.leads.edit')->whereNumber('id');
        Route::post('/leads', [AppController::class, 'storeLead'])->name('app.leads.store');
        Route::post('/leads/{id}', [AppController::class, 'updateLead'])->name('app.leads.update')->whereNumber('id');
        Route::post('/leads/{id}/stage', [AppController::class, 'updateLeadStage'])->name('app.leads.stage')->whereNumber('id');
        Route::delete('/leads/{id}', [AppController::class, 'deleteLead'])->name('app.leads.delete')->whereNumber('id');

        Route::get('/crm/trials', [AppController::class, 'crmTrials'])->name('app.crm.trials');
        Route::get('/trials', [AppController::class, 'crmTrials'])->name('app.trials.index');
        Route::post('/crm/trials', [AppController::class, 'storeTrial'])->name('app.crm.trials.store');
        Route::post('/crm/trials/{id}/status', [AppController::class, 'updateTrialStatus'])->name('app.crm.trials.status')->whereNumber('id');
        Route::delete('/crm/trials/{id}', [AppController::class, 'deleteTrial'])->name('app.crm.trials.delete')->whereNumber('id');

        Route::get('/crm/enquiries', [AppController::class, 'crmEnquiries'])->name('app.crm.enquiries');
        Route::post('/crm/enquiries', [AppController::class, 'storeEnquiry'])->name('app.crm.enquiries.store');
        Route::get('/crm/conversions', [AppController::class, 'crmConversions'])->name('app.crm.conversions');
        Route::get('/crm/reports', [AppController::class, 'crmReports'])->name('app.crm.reports');
    });

    // 12. Report & Finance
    Route::middleware(['feature:reports_finance', 'role:super_admin,gym_owner,gym_manager,accountant'])->group(function () {
        Route::get('/finance/member-report', [AppController::class, 'expenseReport'])->name('app.finance.member-report');
        Route::get('/finance/member-report/pdf', [AppController::class, 'memberReportPdf'])->name('app.finance.member-report.pdf');
        Route::get('/finance/expense-report', [AppController::class, 'expenseReport'])->name('app.finance.expense-report');
        Route::get('/finance/expense-report/pdf', [AppController::class, 'memberReportPdf'])->name('app.finance.expense-report.pdf');
        Route::get('/reports', [AppController::class, 'expenseReport'])->name('app.reports.index');
        Route::get('/expenses', [AppController::class, 'expenses'])->name('app.expenses.index');
        Route::post('/expenses', [AppController::class, 'storeExpense'])->name('app.expenses.store');
        Route::delete('/expenses/{id}', [AppController::class, 'deleteExpense'])->name('app.expenses.delete')->whereNumber('id');
        Route::post('/expenses/categories', [AppController::class, 'storeExpenseCategory'])->name('app.expenses.categories.store');
    });

    Route::middleware(['feature:reports_finance', 'role:super_admin,gym_owner,accountant'])->group(function () {
        Route::get('/finance/balance-sheet', [AppController::class, 'balanceSheet'])->name('app.finance.balance-sheet');
        Route::get('/finance/balance-sheet/pdf', [AppController::class, 'balanceSheetPdf'])->name('app.finance.balance-sheet.pdf');
        Route::get('/balance-sheet', [AppController::class, 'balanceSheet'])->name('app.balance-sheet.index');
    });

    // 13 & 14. Inventory & Equipment Maintenance
    Route::middleware(['feature:inventory_stock,equipment_maintenance', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant,staff'])->group(function () {
        Route::get('/inventory', [AppController::class, 'inventory'])->name('app.inventory.index');
    });

    Route::middleware(['feature:inventory_stock', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant,staff'])->group(function () {
        Route::post('/inventory/items', [AppController::class, 'storeInventoryItem'])->name('app.inventory.items.store');
        Route::post('/inventory/items/{id}', [AppController::class, 'updateInventoryItem'])->name('app.inventory.items.update')->whereNumber('id');
        Route::post('/inventory/items/{id}/adjust', [AppController::class, 'adjustInventoryStock'])->name('app.inventory.items.adjust')->whereNumber('id');
        Route::delete('/inventory/items/{id}', [AppController::class, 'deleteInventoryItem'])->name('app.inventory.items.delete')->whereNumber('id');
    });

    Route::middleware(['feature:equipment_maintenance', 'role:super_admin,gym_owner,gym_manager,receptionist,accountant,staff'])->group(function () {
        Route::post('/inventory/equipment', [AppController::class, 'storeEquipment'])->name('app.inventory.equipment.store');
        Route::post('/inventory/equipment/{id}', [AppController::class, 'updateEquipment'])->name('app.inventory.equipment.update')->whereNumber('id');
        Route::post('/inventory/equipment/{id}/maintenance', [AppController::class, 'recordEquipmentMaintenance'])->name('app.inventory.equipment.maintenance.store')->whereNumber('id');
        Route::delete('/inventory/equipment/{id}', [AppController::class, 'deleteEquipment'])->name('app.inventory.equipment.delete')->whereNumber('id');
    });

    // 15. Biometric & IoT Devices
    Route::middleware(['feature:hikvision_iot', 'role:super_admin,gym_owner,gym_manager'])->group(function () {
        Route::get('/devices', [AppController::class, 'devices'])->name('app.devices.index');
        Route::post('/devices', [AppController::class, 'storeDevice'])->name('app.devices.store');
        Route::post('/devices/{id}/test', [AppController::class, 'testDevice'])->name('app.devices.test')->whereNumber('id');
        Route::delete('/devices/{id}', [AppController::class, 'deleteDevice'])->name('app.devices.delete')->whereNumber('id');
    });

    // 16. Staff Management (Owner Only)
    Route::middleware(['feature:staff_roles', 'role:super_admin,gym_owner'])->group(function () {
        Route::get('/staff', [AppController::class, 'staff'])->name('app.staff.index');
        Route::post('/staff', [AppController::class, 'storeStaff'])->name('app.staff.store');
        Route::post('/staff/{id}', [AppController::class, 'updateStaff'])->name('app.staff.update');
        Route::delete('/staff/{id}', [AppController::class, 'deleteStaff'])->name('app.staff.delete');
        Route::post('/staff/{id}/toggle-status', [AppController::class, 'toggleStaffStatus'])->name('app.staff.toggle-status');
        Route::post('/staff/{id}/reset-password', [AppController::class, 'resetStaffPassword'])->name('app.staff.reset-password');
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
