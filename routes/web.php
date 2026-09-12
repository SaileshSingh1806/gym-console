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

    // Members
    Route::get('/members', [AppController::class, 'members'])->name('app.members.index');
    Route::get('/members/create', [AppController::class, 'createMember'])->name('app.members.create');
    Route::get('/members/lookup-phone', [AppController::class, 'lookupMemberPhone'])->name('app.members.lookup-phone');
    Route::get('/members/{id}', [AppController::class, 'showMember'])->name('app.members.show');
    Route::post('/members', [AppController::class, 'storeMember'])->name('app.members.store');
    Route::post('/members/{id}', [AppController::class, 'updateMember'])->name('app.members.update');
    Route::delete('/members/{id}', [AppController::class, 'deleteMember'])->name('app.members.delete');
    Route::post('/members/{id}/collect-fee', [AppController::class, 'collectMemberFee'])->name('app.members.collect-fee');
    Route::post('/members/{id}/freeze', [AppController::class, 'toggleFreezeMember'])->name('app.members.freeze');
    Route::post('/members/{id}/add-subscription', [AppController::class, 'addMemberSubscription'])->name('app.members.add-subscription');
    Route::post('/members/{id}/add-pt-package', [AppController::class, 'addPtPackage'])->name('app.members.add-pt-package');
    Route::post('/members/{id}/measurements', [AppController::class, 'storeMemberMeasurement'])->name('app.members.store-measurement');

    // Memberships & Billing
    Route::get('/memberships', [AppController::class, 'memberships'])->name('app.memberships.index');
    Route::post('/membership-plans', [AppController::class, 'storeMembershipPlan'])->name('app.membership-plans.store');
    Route::post('/membership-plans/{id}', [AppController::class, 'updateMembershipPlan'])->name('app.membership-plans.update');
    Route::delete('/membership-plans/{id}', [AppController::class, 'deleteMembershipPlan'])->name('app.membership-plans.delete');
    Route::get('/payments', [AppController::class, 'payments'])->name('app.payments.index');
    Route::post('/payments', [AppController::class, 'storePayment'])->name('app.payments.store');
    Route::post('/payments/{id}/reverse', [AppController::class, 'reversePayment'])->name('app.payments.reverse');
    Route::get('/invoices/{id}', [AppController::class, 'showInvoice'])->name('app.invoices.show');

    // Attendance
    Route::get('/attendance', [AppController::class, 'attendance'])->name('app.attendance.index');
    Route::post('/attendance', [AppController::class, 'storeCheckin'])->name('app.attendance.store');

    // Trainers & Classes
    Route::get('/trainers', [AppController::class, 'trainers'])->name('app.trainers.index');
    Route::post('/trainers', [AppController::class, 'storeTrainer'])->name('app.trainers.store');
    Route::get('/classes', [AppController::class, 'classes'])->name('app.classes.index');

    // Workouts & Diets
    Route::get('/workouts', [AppController::class, 'workouts'])->name('app.workouts.index');
    Route::get('/diets', [AppController::class, 'diets'])->name('app.diets.index');

    // Leads, Expenses, Inventory
    Route::get('/leads', [AppController::class, 'leads'])->name('app.leads.index');
    Route::get('/expenses', [AppController::class, 'expenses'])->name('app.expenses.index');
    Route::get('/inventory', [AppController::class, 'inventory'])->name('app.inventory.index');

    // IoT & Devices
    Route::get('/devices', [AppController::class, 'devices'])->name('app.devices.index');
    Route::post('/devices', [AppController::class, 'storeDevice'])->name('app.devices.store');
    Route::post('/devices/{id}/test', [AppController::class, 'testDevice'])->name('app.devices.test');

    // Staff & Team Management
    Route::get('/staff', [AppController::class, 'staff'])->name('app.staff.index');
    Route::post('/staff', [AppController::class, 'storeStaff'])->name('app.staff.store');
    Route::post('/staff/{id}', [AppController::class, 'updateStaff'])->name('app.staff.update');
    Route::delete('/staff/{id}', [AppController::class, 'deleteStaff'])->name('app.staff.delete');
    Route::post('/staff/{id}/toggle-status', [AppController::class, 'toggleStaffStatus'])->name('app.staff.toggle-status');

    // SaaS Subscription & Settings
    Route::get('/subscription', [AppController::class, 'subscription'])->name('app.subscription.index');
    Route::post('/subscription/upgrade', [AppController::class, 'upgradePlan'])->name('app.subscription.upgrade');
    Route::post('/coupon/validate', [AppController::class, 'validateCoupon'])->name('app.coupon.validate');
    Route::get('/settings', [AppController::class, 'settings'])->name('app.settings.index');
    Route::post('/settings', [AppController::class, 'updateSettings'])->name('app.settings.update');
});

// SaaS Super Admin Routes (Fully Editable Platform Administration)
Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

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
    Route::post('/subscriptions/{id}', [AdminController::class, 'updateSubscription'])->name('admin.subscriptions.update');
    Route::post('/subscriptions/manual-payment', [AdminController::class, 'recordManualPayment'])->name('admin.subscriptions.manual-payment');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Activity Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::post('/logs/clear', [AdminController::class, 'clearLogs'])->name('admin.logs.clear');

    // Global Platform Settings (General, Logo/Favicon, Email/SMTP, Razorpay Payment, Site SEO)
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/general', [AdminController::class, 'updateGeneralSettings'])->name('admin.settings.general');
    Route::post('/settings/email', [AdminController::class, 'updateEmailSettings'])->name('admin.settings.email');
    Route::post('/settings/email/test', [AdminController::class, 'sendTestEmail'])->name('admin.settings.email.test');
    Route::post('/settings/payment', [AdminController::class, 'updatePaymentSettings'])->name('admin.settings.payment');
    Route::post('/settings/seo', [AdminController::class, 'updateSeoSettings'])->name('admin.settings.seo');
});
