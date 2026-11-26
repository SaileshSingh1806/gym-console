<!DOCTYPE html>
@php
    $authUser = auth()->user();
    $isMemberUser = auth()->check() && $authUser->isMember();
    $isPureTrainer = $authUser && ($authUser->role === 'trainer' || ($authUser->isTrainer() && !$authUser->isGymOwner() && !$authUser->isSuperAdmin()));
    $isStealthApp = $isMemberUser || $isPureTrainer || request()->routeIs('trainer.*');
    $serverThemeMode = 'dark';
    if (!empty($savedThemeSettings)) {
        $st = is_array($savedThemeSettings) ? $savedThemeSettings : json_decode($savedThemeSettings, true);
        if (!empty($st['mode']) && $st['mode'] === 'light' && !$isStealthApp) {
            $serverThemeMode = 'light';
        }
    }
    $resolvedThemeKey = $themeStorageKey ?? ($isMemberUser ? 'member_portal' : ($isStealthApp ? 'trainer_portal' : 'admin'));
    $effectiveSavedTheme = $isStealthApp ? ['mode' => 'dark', 'accent' => 'luxury-studio', 'isCustom' => true, 'customHex' => '#C5A880'] : ($savedThemeSettings ?? null);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isStealthApp ? 'dark' : $serverThemeMode }} h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <x-theme-head-styles :saved-theme="$effectiveSavedTheme" :storage-key="$resolvedThemeKey" />

    <title>{{ $title ?? ($platformSettings['app_name'] ?? 'GymDeskHub') }}</title>

    @if(!empty($platformSettings['favicon_url']))
        <link rel="icon" type="image/x-icon" href="{{ $platformSettings['favicon_url'] }}">
        <link rel="shortcut icon" href="{{ $platformSettings['favicon_url'] }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=4">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=4">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=4">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=4">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=4">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <x-tailwind-config />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-design-system />
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @if(!empty($platformSettings['custom_header_scripts']))
        {!! $platformSettings['custom_header_scripts'] !!}
    @endif
    <x-workspace-ui-styles />
    @stack('styles')
</head>
<body class="workspace-shell flex min-h-screen min-h-[100dvh] lg:h-screen lg:overflow-hidden antialiased {{ $isStealthApp ? 'bg-[#0F1115] text-[#F4EDE4] dark:bg-[#0F1115] selection:bg-[#C5A880]/30' : 'ds-body bg-canvas text-fg' }}" :class="{ 'overflow-hidden': sidebarOpen }" x-data="{ sidebarOpen: false }">

    @if(!$authUser?->isMember() && !$isPureTrainer)
    @php
        $user = auth()->user();
        $is = fn (string ...$patterns) => request()->routeIs(...$patterns);

        $tenant = $user->tenant ?? null;
        $gymLogo = $tenant?->logo_url;
        $gymName = $tenant?->name ?? ($platformSettings['app_name'] ?? 'GymDeskHub');

        $canGymOps = $user->isGymOwner()
            || ($user->hasPermission('members.view') && $user->hasFeature('members_management'))
            || (($user->hasPermission('memberships.view') || $user->hasPermission('plans.view') || $user->hasPermission('plans.create') || $user->hasPermission('plans.edit') || $user->hasPermission('memberships.create') || $user->hasPermission('memberships.edit') || $user->hasPermission('memberships.manage')) && $user->hasFeature('memberships_billing'))
            || ($user->hasPermission('attendance.view') && $user->hasFeature('attendance_checkin'))
            || ($user->hasPermission('payments.view') && $user->hasFeature('payments_pos'))
            || ($user->hasAnyPermission(['crm.leads.view', 'crm.leads.create', 'crm.view']));

        $canFitness = ($user->hasPermission('classes.view') && $user->hasFeature('group_classes'))
            || ($user->hasPermission('trainers.view') && $user->hasFeature('trainers_management'))
            || ($user->hasPermission('pt.view') && $user->hasFeature('personal_training'))
            || ($user->hasPermission('workouts.view') && $user->hasFeature('workout_plans'))
            || ($user->hasPermission('diets.view') && $user->hasFeature('diet_nutrition'))
            || ($user->hasPermission('services.view') && $user->hasFeature('gym_services'));

        $canBiz = ($user->hasAnyPermission(['crm.view', 'crm.leads.view', 'crm.leads.create', 'crm.trials', 'crm.reports']) && $user->hasFeature('crm_leads'))
            || ($user->hasAnyPermission(['reports.member_report', 'reports.balance_sheet', 'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.categories']) && $user->hasFeature('reports_finance'))
            || ($user->hasAnyPermission(['inventory.view', 'equipment.maintenance']) && $user->hasAnyFeature(['inventory_stock', 'equipment_maintenance']));

        $canAdmin = ($user->hasAnyPermission(['staff.view', 'staff.create', 'roles.manage']) && $user->hasFeature('staff_roles'))
            || (($user->hasPermission('devices.view') || $user->hasPermission('devices.manage')) && $user->hasFeature('biometric_devices'))
            || $user->hasAnyPermission(['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.categories'])
            || $user->isGymOwner()
            || $user->isSuperAdmin()
            || $user->hasPermission('settings.manage');

        $expensePerms = ['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.categories'];
        $crmPerms = ['crm.view', 'crm.leads.view', 'crm.leads.create', 'crm.trials', 'crm.reports'];

        $tenantBranches = $user->tenant ? $user->tenant->branches : collect();
        $allowedBranchIds = $user->tenant ? $user->tenant->allowedBranches()->pluck('id')->toArray() : [];
        $currentActiveBranch = $user->currentBranch();
    @endphp

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen"
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-[100] bg-black/60 lg:hidden"
         @click="sidebarOpen = false"
         x-cloak></div>

    <!-- Sidebar: drawer on mobile, fixed column on desktop -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-[101] w-72 max-w-[85vw] lg:w-64 bg-surface border-r border-line flex flex-col transition-transform duration-200 ease-out lg:static lg:inset-auto lg:h-screen lg:shrink-0 lg:z-auto">

        <!-- Brand -->
        <div class="h-16 shrink-0 flex items-center justify-between px-5 border-b border-line">
            <a href="{{ route('app.dashboard') }}" class="flex items-center gap-2.5 min-w-0 focus:outline-none">
                @if(!empty($gymLogo))
                    <img src="{{ $gymLogo }}" alt="{{ $gymName }}" class="h-8 w-auto max-w-[120px] rounded-md object-contain">
                    <span class="text-sm font-semibold text-fg truncate max-w-[130px]">{{ $gymName }}</span>
                @else
                    <img src="{{ asset('images/gymdeskhub_horizontal_dark.png') }}" alt="{{ $gymName }}" class="h-8 w-auto max-w-[170px] object-contain hidden dark:block">
                    <img src="{{ asset('images/gymdeskhub_horizontal_light.png') }}" alt="{{ $gymName }}" class="h-8 w-auto max-w-[170px] object-contain block dark:hidden">
                @endif
            </a>
            <button @click="sidebarOpen = false" type="button" aria-label="Close sidebar" class="lg:hidden ds-btn ds-btn-ghost !h-8 !w-8 !p-0">
                <x-icon name="x" :size="16" />
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 pb-4 pt-3" aria-label="Main">
            <div class="space-y-0.5">
                <x-nav-item :href="route('app.dashboard')" icon="home" :active="$is('app.dashboard')">Dashboard</x-nav-item>
            </div>

            @if($canGymOps)
            <div class="ds-nav-section">Gym operations</div>
            <div class="space-y-0.5">
                @if($user->isGymOwner() || $user->hasAnyPermission(['crm.leads.view', 'crm.leads.create', 'crm.view']))
                    <x-nav-item :href="route('app.crm.enquiries')" icon="chat" :active="$is('app.crm.enquiries', 'app.enquiries.*')">Enquiries</x-nav-item>
                @endif

                @if($user->hasPermission('members.view') && $user->hasFeature('members_management'))
                    <x-nav-item :href="route('app.members.index')" icon="users" :active="$is('app.members.*')">Members</x-nav-item>

                    @if($user->isGymOwner() || $user->hasPermission('agreements.view'))
                        <x-nav-item :href="route('app.agreements.index')" icon="document" :active="$is('app.agreements.*')">Agreements &amp; health</x-nav-item>
                    @endif
                @endif

                @if(($user->isGymOwner() || $user->hasPermission('memberships.view') || $user->hasPermission('plans.view') || $user->hasPermission('plans.create') || $user->hasPermission('plans.edit') || $user->hasPermission('memberships.create') || $user->hasPermission('memberships.edit') || $user->hasPermission('memberships.manage')) && $user->hasFeature('memberships_billing'))
                    <x-nav-item :href="route('app.memberships.index')" icon="ticket" :active="$is('app.memberships.*', 'app.membership-plans.*')">Memberships</x-nav-item>
                @endif

                @if($user->hasPermission('attendance.view') && $user->hasFeature('attendance_checkin'))
                    <x-nav-item :href="route('app.attendance.index')" icon="clock" :active="$is('app.attendance.*')">Attendance</x-nav-item>
                @endif

                @if($user->hasPermission('payments.view') && $user->hasFeature('payments_pos'))
                    <x-nav-item :href="route('app.payments.index')" icon="wallet" :active="$is('app.payments.*')">Payments &amp; POS</x-nav-item>
                @endif
            </div>
            @endif

            @if($canFitness)
            <div class="ds-nav-section">Fitness &amp; coaching</div>
            <div class="space-y-0.5">
                @if($user->hasPermission('classes.view') && $user->hasFeature('group_classes'))
                    <x-nav-item :href="route('app.classes.index')" icon="calendar" :active="$is('app.classes.*')">Training &amp; classes</x-nav-item>
                @endif

                @if($user->hasPermission('trainers.view') && $user->hasFeature('trainers_management'))
                    <x-nav-item :href="route('app.trainers.index')" icon="team" :active="$is('app.trainers.*')">Trainers</x-nav-item>
                @endif

                @if($user->hasPermission('pt.view') && $user->hasFeature('personal_training'))
                    <x-nav-item :href="route('app.pt.index')" icon="user" :active="$is('app.pt.*', 'app.pt-plans.*')">Personal training</x-nav-item>
                @endif

                @if($user->hasPermission('workouts.view') && $user->hasFeature('workout_plans'))
                    <x-nav-item :href="route('app.workouts.index')" icon="bolt" :active="$is('app.workouts.*')">Workout plans</x-nav-item>
                @endif

                @if($user->hasPermission('diets.view') && $user->hasFeature('diet_nutrition'))
                    <x-nav-item :href="route('app.diets.index')" icon="book" :active="$is('app.diets.*')">Diet &amp; nutrition</x-nav-item>
                @endif

                @if($user->hasPermission('services.view') && $user->hasFeature('gym_services'))
                    <x-nav-item :href="route('app.services.index')" icon="grid" :active="$is('app.services.*')">Services</x-nav-item>
                @endif

                @if($user->isGymOwner() || $user->hasAnyPermission(['crm.leads.view', 'crm.view', 'members.view']))
                    @php
                        $pendingFeedbacksCount = \App\Models\CrmFeedback::where('tenant_id', $user->tenant_id)->whereIn('status', ['new', 'pending', 'open'])->count();
                    @endphp
                    <x-nav-item :href="route('app.feedback.index')" icon="message" :active="$is('app.feedback.*')" :count="$pendingFeedbacksCount ?: null">Member feedback</x-nav-item>
                @endif
            </div>
            @endif

            @if($canBiz)
            <div class="ds-nav-section">Business &amp; growth</div>
            <div class="space-y-0.5">
                {{-- CRM --}}
                @if($user->hasAnyPermission($crmPerms) && $user->hasFeature('crm_leads'))
                    <x-nav-group label="CRM & leads" icon="trending-up" :active="$is('app.crm.*', 'app.leads.*', 'app.trials.*')">
                        @if($user->hasAnyPermission(['crm.view', 'crm.reports']))
                            <x-nav-sub :href="route('app.crm.dashboard')" :active="$is('app.crm.dashboard', 'app.crm.index')">CRM dashboard</x-nav-sub>
                        @endif
                        @if($user->hasAnyPermission(['crm.view', 'crm.leads.view', 'crm.leads.create']))
                            <x-nav-sub :href="route('app.leads.index')" :active="$is('app.leads.*', 'app.crm.leads')">Leads</x-nav-sub>
                        @endif
                        @if($user->hasAnyPermission(['crm.view', 'crm.trials']))
                            <x-nav-sub :href="route('app.crm.trials')" :active="$is('app.crm.trials', 'app.trials.*')">Trials</x-nav-sub>
                        @endif
                        @if($user->hasAnyPermission(['crm.view', 'crm.leads.view', 'crm.reports']))
                            <x-nav-sub :href="route('app.crm.conversions')" :active="$is('app.crm.conversions')">Conversions</x-nav-sub>
                        @endif
                        @if($user->hasAnyPermission(['crm.view', 'crm.reports']))
                            <x-nav-sub :href="route('app.crm.reports') . '#targets'" :active="$is('app.crm.reports') && request()->has('targets')">Sales targets</x-nav-sub>
                            <x-nav-sub :href="route('app.crm.reports')" :active="$is('app.crm.reports') && !request()->has('targets')">CRM reports</x-nav-sub>
                        @endif
                    </x-nav-group>
                @endif

                {{-- WhatsApp --}}
                @if($user->hasFeature('whatsapp_marketing') && $user->hasAnyPermission(['whatsapp.view', 'whatsapp.contacts', 'whatsapp.campaigns', 'whatsapp.templates', 'whatsapp.inbox', 'whatsapp.settings']))
                    <x-nav-group label="WhatsApp" icon="message" :active="$is('app.whatsapp.*')">
                        @if($user->hasPermission('whatsapp.view'))
                            <x-nav-sub :href="route('app.whatsapp.dashboard')" :active="$is('app.whatsapp.dashboard')">Dashboard</x-nav-sub>
                        @endif
                        @if($user->hasPermission('whatsapp.contacts'))
                            <x-nav-sub :href="route('app.whatsapp.contacts.index')" :active="$is('app.whatsapp.contacts.index')">Contacts</x-nav-sub>
                            <x-nav-sub :href="route('app.whatsapp.contacts.import')" :active="$is('app.whatsapp.contacts.import')">Import contacts</x-nav-sub>
                        @endif
                        @if($user->hasPermission('whatsapp.templates'))
                            <x-nav-sub :href="route('app.whatsapp.templates.index')" :active="$is('app.whatsapp.templates.*')">Message templates</x-nav-sub>
                        @endif
                        @if($user->hasPermission('whatsapp.campaigns'))
                            <x-nav-sub :href="route('app.whatsapp.campaigns.index')" :active="$is('app.whatsapp.campaigns.index')">Bulk campaigns</x-nav-sub>
                        @endif
                        @if($user->hasPermission('whatsapp.inbox'))
                            <x-nav-sub :href="route('app.whatsapp.inbox')" :active="$is('app.whatsapp.inbox')">Inbox</x-nav-sub>
                        @endif
                        @if($user->hasPermission('whatsapp.settings'))
                            <x-nav-sub :href="route('app.whatsapp.automations.index')" :active="$is('app.whatsapp.automations.*')">Automations</x-nav-sub>
                            <x-nav-sub :href="route('app.whatsapp.settings')" :active="$is('app.whatsapp.settings')">Settings</x-nav-sub>
                        @endif
                    </x-nav-group>
                @endif

                {{-- Reports & finance --}}
                @if($user->hasAnyPermission(['reports.member_report', 'reports.balance_sheet', ...$expensePerms]) && $user->hasFeature('reports_finance'))
                    <x-nav-group label="Reports & finance" icon="calculator" :active="$is('app.finance.*', 'app.reports.*', 'app.expenses.*', 'app.balance-sheet.*')">
                        @if($user->hasPermission('reports.member_report'))
                            <x-nav-sub :href="route('app.finance.expense-report')" :active="$is('app.finance.expense-report', 'app.finance.member-report', 'app.reports.*')">Member report</x-nav-sub>
                        @endif
                        @if($user->hasPermission('reports.balance_sheet'))
                            <x-nav-sub :href="route('app.finance.balance-sheet')" :active="$is('app.finance.balance-sheet', 'app.balance-sheet.*')">Balance sheet</x-nav-sub>
                        @endif
                        @if($user->hasAnyPermission($expensePerms))
                            <x-nav-sub :href="route('app.expenses.index')" :active="$is('app.expenses.*')">Expenses</x-nav-sub>
                        @endif
                    </x-nav-group>
                @endif

                {{-- Inventory --}}
                @if($user->hasAnyPermission(['inventory.view', 'equipment.maintenance']) && $user->hasAnyFeature(['inventory_stock', 'equipment_maintenance']))
                    <x-nav-item :href="route('app.inventory.index')" icon="cube" :active="$is('app.inventory.*')">Inventory &amp; assets</x-nav-item>
                @endif
            </div>
            @endif

            @if($canAdmin)
            <div class="ds-nav-section">Administration</div>
            <div class="space-y-0.5">
                @if($user->hasAnyPermission(['staff.view', 'staff.create', 'roles.manage']) && $user->hasFeature('staff_roles'))
                    <x-nav-group label="Staff & roles" icon="team" :active="$is('app.staff.*', 'app.roles.*', 'app.payroll.*')">
                        @if($user->hasAnyPermission(['staff.view', 'staff.create', 'staff.edit']))
                            <x-nav-sub :href="route('app.staff.index')" :active="$is('app.staff.*')">Staff members</x-nav-sub>
                        @endif
                        @if($user->hasPermission('roles.manage'))
                            <x-nav-sub :href="route('app.roles.index')" :active="$is('app.roles.*')">Roles &amp; permissions</x-nav-sub>
                        @endif
                        <x-nav-sub :href="route('app.payroll.index')" :active="$is('app.payroll.*')">Salary &amp; payslips</x-nav-sub>
                    </x-nav-group>
                @endif

                @if($user->hasAnyPermission($expensePerms) || $user->isGymOwner())
                    <x-nav-item :href="route('app.expenses.index')" icon="receipt" :active="$is('app.expenses.*')">Expenses</x-nav-item>
                @endif

                @if(($user->hasPermission('devices.view') || $user->hasPermission('devices.manage')) && $user->hasFeature('biometric_devices'))
                    <x-nav-item :href="route('app.devices.index')" icon="fingerprint" :active="$is('app.devices.*')">Biometric devices</x-nav-item>
                @endif

                @if($user->isGymOwner() || $user->isSuperAdmin())
                    <x-nav-item :href="route('app.subscription.index')" icon="ticket" :active="$is('app.subscription.*')">Subscription &amp; billing</x-nav-item>
                @endif

                @if($user->hasPermission('settings.manage') || $user->isGymOwner())
                    <x-nav-item :href="route('app.settings.index')" icon="cog" :active="$is('app.settings.*')">Settings</x-nav-item>
                @endif
            </div>
            @endif

            <div class="ds-nav-section">Help</div>
            <div class="space-y-0.5">
                <x-nav-item :href="route('app.support.index')" icon="lifebuoy" :active="$is('app.support.*')">Support tickets</x-nav-item>
            </div>
        </nav>

        <!-- Branch switcher + account -->
        <div class="shrink-0 border-t border-line p-3 space-y-2">
            @if($tenantBranches->count() > 1)
            <div class="relative" x-data="{ branchDropdownOpen: false }">
                <button type="button"
                        @click="branchDropdownOpen = !branchDropdownOpen"
                        class="w-full flex items-center justify-between gap-2 min-h-[40px] px-3 py-1.5 rounded-lg bg-raised border border-line hover:border-line-strong text-left cursor-pointer transition-colors">
                    <span class="min-w-0">
                        <span class="block text-[13px] font-medium text-fg truncate">{{ $currentActiveBranch->name ?? 'Main Branch' }}</span>
                        <span class="block text-xs text-fg-subtle truncate">{{ $currentActiveBranch->code ?? 'MAIN' }}</span>
                    </span>
                    <span class="text-fg-subtle transition-transform duration-150" :class="branchDropdownOpen ? 'rotate-180' : ''">
                        <x-icon name="chevron-down" :size="14" />
                    </span>
                </button>

                <div x-show="branchDropdownOpen" @click.away="branchDropdownOpen = false" x-cloak
                     class="absolute bottom-full left-0 mb-2 w-full rounded-xl bg-surface border border-line-strong p-1.5 z-50 space-y-0.5 max-h-64 overflow-y-auto">
                    <div class="px-2.5 py-1.5 flex items-center justify-between text-xs text-fg-subtle">
                        <span>Switch branch</span>
                        <span class="tabular-nums">{{ count($allowedBranchIds) }}/{{ $tenantBranches->count() }}</span>
                    </div>

                    @foreach($tenantBranches as $tb)
                        @php $isAllowed = in_array($tb->id, $allowedBranchIds, true); @endphp
                        @if($isAllowed)
                            @php $isCurrentBranch = $currentActiveBranch && $currentActiveBranch->id === $tb->id; @endphp
                            <form action="{{ route('app.branches.switch', $tb->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-between gap-2 px-2.5 py-2 rounded-lg text-left cursor-pointer transition-colors {{ $isCurrentBranch ? 'bg-raised text-fg' : 'text-fg-muted hover:bg-raised hover:text-fg' }}">
                                    <span class="min-w-0">
                                        <span class="block text-[13px] font-medium truncate">{{ $tb->name }}@if($tb->is_main) <span class="text-xs font-normal text-fg-subtle">(main)</span>@endif</span>
                                        <span class="block text-xs text-fg-subtle truncate">{{ $tb->code ?? 'MAIN' }}{{ $tb->city ? ' · '.$tb->city : '' }}</span>
                                    </span>
                                    @if($isCurrentBranch)
                                        <x-icon name="check" :size="14" class="text-accent-fg" />
                                    @endif
                                </button>
                            </form>
                        @else
                            <a href="{{ route('app.subscription.index') }}" title="Upgrade your plan to use this branch"
                               class="w-full flex items-center justify-between gap-2 px-2.5 py-2 rounded-lg text-fg-subtle hover:bg-raised transition-colors">
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-medium truncate">{{ $tb->name }}</span>
                                    <span class="block text-xs truncate">Upgrade to use</span>
                                </span>
                                <x-icon name="lock" :size="14" />
                            </a>
                        @endif
                    @endforeach

                    <div class="pt-1 mt-1 border-t border-line">
                        <a href="{{ route('app.settings.index', ['tab' => 'branches']) }}" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-[13px] font-medium text-fg-muted hover:bg-raised hover:text-fg transition-colors">
                            <x-icon name="plus" :size="14" />
                            <span>Manage branches</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-full bg-raised border border-line flex items-center justify-center text-xs font-semibold text-fg-muted">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-fg truncate">{{ $user->name }}</p>
                        <p class="text-xs text-fg-subtle capitalize truncate">{{ str_replace('_', ' ', $user->role) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign out" aria-label="Sign out" class="ds-btn ds-btn-ghost !h-8 !w-8 !p-0 hover:!text-danger cursor-pointer">
                        <x-icon name="logout" :size="16" />
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endif

    <!-- Main Content Shell -->
    <div class="flex-1 flex flex-col min-w-0 w-full min-h-screen lg:h-screen lg:overflow-y-auto {{ $isStealthApp ? 'bg-[#0F1115]' : 'bg-canvas' }}">
        @php
            $currentTenant = auth()->user()->tenant;
        @endphp
        @if($currentTenant && $currentTenant->isSubscriptionExpired())
            <div class="bg-danger/10 border-b border-danger/30 px-4 sm:px-6 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                <p class="text-fg-muted"><span class="font-semibold text-danger">Subscription expired.</span> Gym operations are locked until you renew or choose a plan.</p>
                <a href="{{ route('app.subscription.index') }}" class="ds-btn ds-btn-primary !h-8 shrink-0">
                    <span>Renew plan</span>
                    <x-icon name="arrow-right" :size="14" />
                </a>
            </div>
        @endif

        <!-- Top Navbar -->
        <header class="h-16 {{ $isStealthApp ? 'bg-[#0F1115]/90 border-b border-neutral-800/80 text-[#F4EDE4] backdrop-blur-md' : 'bg-surface border-b border-line' }} px-4 md:px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                @if(request()->routeIs('trainer.*') || $isPureTrainer)
                    @php
                        $trainerTenant = auth()->user()->tenant ?? null;
                        $tGymLogo = $trainerTenant?->logo_url;
                        $resolvedTrainerLogo = $tGymLogo ? (\Illuminate\Support\Str::startsWith($tGymLogo, ['http://', 'https://', '/']) ? $tGymLogo : asset('storage/' . $tGymLogo)) : null;
                        $tGymName = $trainerTenant?->name ?? 'GymDeskHub';
                    @endphp
                    @if(! $isPureTrainer)
                        <button @click="sidebarOpen = true" type="button" aria-label="Open Navigation Menu" class="lg:hidden p-2 -ml-1 rounded-xl text-neutral-400 hover:text-white hover:bg-[#171A22] transition-colors shrink-0 cursor-pointer">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    @endif
                    <a href="{{ route('trainer.workspace') }}" class="flex items-center gap-2.5 min-w-0 group">
                        @if(!empty($resolvedTrainerLogo))
                            <img src="{{ $resolvedTrainerLogo }}" alt="{{ $tGymName }}" class="h-8 sm:h-9 w-auto max-w-[130px] rounded-lg object-contain">
                        @else
                            <div class="w-9 h-9 rounded-xl bg-[#171A22] border border-[#C5A880]/35 flex items-center justify-center text-[#C5A880] font-black text-xs shadow-sm shrink-0">
                                {{ strtoupper(substr($tGymName, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="font-extrabold text-white text-sm sm:text-base md:text-lg tracking-tight truncate leading-tight">{{ $tGymName }}</span>
                            <span class="text-[11px] sm:text-xs text-[#C5A880] font-bold uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#C5A880] animate-pulse shrink-0"></span>
                                <span class="truncate">Trainer App</span>
                            </span>
                        </div>
                    </a>
                @elseif(!auth()->user()->isMember())
                    <button @click="sidebarOpen = true" type="button" aria-label="Open navigation menu" class="lg:hidden ds-btn ds-btn-ghost !h-9 !w-9 !p-0 -ml-1 shrink-0 cursor-pointer">
                        <x-icon name="menu" :size="20" />
                    </button>
                    <h1 class="text-base font-semibold text-fg truncate">{{ $header ?? 'Dashboard' }}</h1>
                @else
                    @php
                        $memberTenant = $tenant ?? (auth()->user()->tenant ?? \App\Services\TenantContext::getTenant());
                        $gymLogoRaw = $memberTenant?->logo_url;
                        $resolvedMemberGymLogo = $gymLogoRaw ? (\Illuminate\Support\Str::startsWith($gymLogoRaw, ['http://', 'https://', '/']) ? $gymLogoRaw : asset('storage/' . $gymLogoRaw)) : null;
                        $gymName = $memberTenant?->name ?? 'GymDeskHub';
                    @endphp
                    <a href="{{ route('app.portal') }}" class="flex items-center gap-2.5 sm:gap-3 min-w-0 group">
                        @if(!empty($resolvedMemberGymLogo))
                            <img src="{{ $resolvedMemberGymLogo }}" alt="{{ $gymName }}" class="h-9 sm:h-10 w-auto max-w-[130px] sm:max-w-[160px] rounded-lg object-contain shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-[#171A22] border border-[#C5A880]/35 text-[#C5A880] font-black text-sm flex items-center justify-center shadow-sm shrink-0">
                                {{ strtoupper(substr($gymName, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0 justify-center">
                            <span class="font-extrabold text-white text-base sm:text-lg tracking-tight truncate leading-tight">{{ $gymName }}</span>
                            <span class="text-[11px] text-neutral-400 font-medium tracking-wide truncate hidden xs:block">{{ $memberTenant?->city ?? 'Club Member' }}</span>
                        </div>
                    </a>
                @endif
            </div>

            <!-- Right Telemetry & Widget Badges -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                
                <!-- Staff / admin telemetry -->
                @if(!$authUser?->isMember() && !$isPureTrainer)
                    @php
                        $todayInsideCount = \App\Models\Attendance::where('date', now()->toDateString())->whereNull('check_out')->count();
                        $todayTotalAttendance = \App\Models\Attendance::where('date', now()->toDateString())->count();
                    @endphp
                    <div class="hidden xl:flex items-center gap-2 mr-2 text-xs text-fg-muted">
                        <span><span class="font-semibold text-fg tabular-nums">{{ $todayInsideCount }}</span> in gym</span>
                        <span class="text-line-strong" aria-hidden="true">/</span>
                        <span><span class="font-semibold text-fg tabular-nums">{{ $todayTotalAttendance }}</span> check-ins today</span>
                    </div>

                    <div x-data="{ stamp: '', init() { const f = () => { this.stamp = new Date().toLocaleString('en-IN', { weekday: 'short', day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit', hour12: true }); }; f(); setInterval(f, 30000); } }"
                         class="hidden lg:block mr-2 text-xs text-fg-muted tabular-nums" x-text="stamp"></div>

                    <x-theme-customizer />

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex ds-btn ds-btn-secondary !h-8">Super admin</a>
                    @endif

                <!-- Notification centre -->
                <div x-data="notificationCenter()" x-init="init()" @click.outside="isOpen = false" @keydown.escape.window="isOpen = false" class="relative">
                    <button type="button"
                            @click="toggleDropdown()"
                            title="Notifications"
                            aria-label="Notifications"
                            class="relative ds-btn ds-btn-ghost !h-9 !w-9 !p-0 cursor-pointer">
                        <x-icon name="bell" :size="18" />
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-accent text-accent-on text-xs font-semibold leading-[18px] text-center tabular-nums"
                                  x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </template>
                    </button>

                    <div x-show="isOpen"
                         x-cloak
                         style="display: none;"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed right-3 sm:absolute sm:right-0 top-16 sm:top-full mt-2 w-[calc(100vw-1.5rem)] sm:w-[400px] rounded-xl bg-surface border border-line-strong shadow-lg z-50 overflow-hidden">

                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-line flex items-center justify-between gap-3">
                            <div>
                                <h4 class="ds-heading">Notifications</h4>
                                <p class="text-xs text-fg-muted tabular-nums" x-text="unreadCount + ' unread'"></p>
                            </div>
                            <template x-if="unreadCount > 0">
                                <button type="button" @click="markAllAsRead()" class="text-xs font-medium text-accent-fg hover:underline cursor-pointer">Mark all as read</button>
                            </template>
                        </div>

                        <!-- Filters -->
                        <div class="px-3 py-2 border-b border-line flex items-center gap-1 overflow-x-auto no-scrollbar text-xs font-medium">
                            <button type="button"
                                    @click="activeFilter = 'all'"
                                    :class="activeFilter === 'all' ? 'bg-raised text-fg' : 'text-fg-muted hover:text-fg'"
                                    class="px-2.5 h-7 rounded-md whitespace-nowrap cursor-pointer transition-colors">
                                All <span class="tabular-nums text-fg-subtle" x-text="counts.all"></span>
                            </button>
                            <template x-for="f in [['whatsapp', 'WhatsApp'], ['renewal', 'Renewals'], ['payment', 'Dues'], ['birthday', 'Birthdays'], ['lead', 'Leads']]" :key="f[0]">
                                <button type="button"
                                        x-show="counts[f[0]] > 0"
                                        @click="activeFilter = f[0]"
                                        :class="activeFilter === f[0] ? 'bg-raised text-fg' : 'text-fg-muted hover:text-fg'"
                                        class="px-2.5 h-7 rounded-md whitespace-nowrap cursor-pointer transition-colors">
                                    <span x-text="f[1]"></span> <span class="tabular-nums text-fg-subtle" x-text="counts[f[0]]"></span>
                                </button>
                            </template>
                        </div>

                        <!-- List -->
                        <div class="max-h-[380px] overflow-y-auto divide-y divide-line">
                            <template x-if="isLoading && notifications.length === 0">
                                <div class="p-8 text-center space-y-3">
                                    <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-accent border-t-transparent"></div>
                                    <p class="text-xs text-fg-muted">Loading notifications…</p>
                                </div>
                            </template>

                            <template x-if="!isLoading && filteredNotifications().length === 0">
                                <div class="p-8 text-center space-y-2">
                                    <div class="w-9 h-9 mx-auto rounded-full bg-raised text-fg-muted flex items-center justify-center">
                                        <x-icon name="check" :size="16" />
                                    </div>
                                    <h5 class="text-sm font-medium text-fg">You're all caught up</h5>
                                    <p class="text-xs text-fg-muted max-w-xs mx-auto">No pending alerts or unread messages.</p>
                                </div>
                            </template>

                            <template x-for="item in filteredNotifications()" :key="item.id">
                                <div class="relative px-4 py-3 transition-colors hover:bg-raised/60" :class="item.is_read ? 'opacity-70 hover:opacity-100' : ''">
                                    <span x-show="!item.is_read" class="absolute left-1.5 top-5 w-1.5 h-1.5 rounded-full bg-accent"></span>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-raised text-fg-muted flex items-center justify-center shrink-0">
                                            <template x-if="item.type === 'whatsapp'"><x-icon name="message" :size="16" /></template>
                                            <template x-if="item.type === 'renewal'"><x-icon name="refresh" :size="16" /></template>
                                            <template x-if="item.type === 'payment'"><x-icon name="currency" :size="16" /></template>
                                            <template x-if="item.type === 'birthday'"><x-icon name="calendar" :size="16" /></template>
                                            <template x-if="item.type === 'lead'"><x-icon name="user-add" :size="16" /></template>
                                            <template x-if="!['whatsapp', 'renewal', 'payment', 'birthday', 'lead'].includes(item.type)"><x-icon name="bell" :size="16" /></template>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <h5 class="text-[13px] font-medium text-fg truncate" x-text="item.title"></h5>
                                                <span class="text-xs text-fg-subtle whitespace-nowrap" x-text="item.time_ago"></span>
                                            </div>
                                            <p class="text-xs text-fg-muted mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>

                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <a :href="item.link"
                                                   :target="item.is_external ? '_blank' : '_self'"
                                                   @click="markAsRead(item.id)"
                                                   class="ds-btn ds-btn-secondary !h-7 !px-2.5 !text-xs">
                                                    <span x-text="item.action_label"></span>
                                                    <x-icon name="chevron-right" :size="12" />
                                                </a>
                                                <template x-if="!item.is_read">
                                                    <button type="button" @click="markAsRead(item.id)" class="text-xs text-fg-subtle hover:text-fg transition-colors cursor-pointer">Dismiss</button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2.5 border-t border-line flex items-center justify-between text-xs font-medium text-fg-muted">
                            <a href="{{ route('app.crm.leads') }}" class="hover:text-fg transition-colors">Leads</a>
                            <a href="{{ route('app.whatsapp.inbox') }}" class="hover:text-fg transition-colors">WhatsApp inbox</a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- User Profile & Account Dropdown -->
                <div class="relative flex items-center pl-0.5 sm:pl-1 z-50" 
                     x-data="{ 
                         userMenuOpen: false, 
                         showPasswordModal: {{ ($errors->has('current_password') || $errors->has('password')) ? 'true' : 'false' }},
                         showCurrentPass: false,
                         showNewPass: false,
                         showConfirmPass: false
                     }">
                    @if($isStealthApp)
                        <!-- Member / Trainer Profile Pill Button (Champagne Stealth) -->
                        <button @click="userMenuOpen = !userMenuOpen" 
                                type="button" 
                                class="group relative flex items-center gap-2 sm:gap-2.5 p-1 sm:pl-3 sm:pr-2.5 sm:py-1 rounded-full bg-[#171A22] hover:bg-neutral-800/90 border border-neutral-800 hover:border-[#C5A880]/40 transition-all cursor-pointer focus:outline-none"
                                title="Account Profile & Settings">
                            <div class="hidden sm:flex flex-col items-end text-right leading-tight">
                                <span class="text-xs font-bold text-white group-hover:text-[#C5A880] transition-colors truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                <span class="text-[9px] font-semibold text-[#C5A880] tracking-wider uppercase">{{ $isMemberUser ? 'Active Member' : 'Personal Coach' }}</span>
                            </div>
                            <div class="relative shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#D4B995] to-[#C5A880] text-neutral-950 font-black text-xs flex items-center justify-center shadow-md shadow-[#C5A880]/20 group-hover:scale-105 transition-transform">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-[#171A22]"></span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-neutral-400 group-hover:text-[#C5A880] transition-transform duration-200 hidden sm:block" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @else
                        <!-- Staff / admin profile button -->
                        <button @click="userMenuOpen = !userMenuOpen"
                                type="button"
                                class="w-8 h-8 rounded-full bg-raised border border-line hover:border-line-strong text-xs font-semibold text-fg flex items-center justify-center cursor-pointer transition-colors"
                                title="Account"
                                aria-label="Account menu">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>
                    @endif

                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen"
                         x-cloak
                         style="display: none;"
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         class="origin-top-right fixed right-2.5 sm:absolute sm:right-0 top-16 sm:top-full mt-2 w-[calc(100vw-1.25rem)] max-w-xs sm:max-w-none sm:w-80 rounded-xl {{ $isStealthApp ? 'bg-[#171A22] border border-neutral-800 text-[#F4EDE4] divide-neutral-800' : 'bg-surface border border-line-strong divide-line' }} shadow-lg z-[9999] overflow-hidden divide-y">
                        
                        <!-- Account Summary Header -->
                        <div class="p-4 {{ $isStealthApp ? 'bg-[#0F1115]/80' : 'bg-surface' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-full {{ $isStealthApp ? 'bg-gradient-to-tr from-[#D4B995] to-[#C5A880] text-neutral-950 font-black' : 'bg-accent text-accent-on font-semibold' }} text-base flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4 class="text-sm font-semibold text-fg truncate">{{ auth()->user()->name }}</h4>
                                    </div>
                                    <p class="text-xs text-fg-muted truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        @php
                                            $role = auth()->user()->role;
                                            $roleBadgeClass = $isMemberUser ? 'bg-[#C5A880]/15 text-[#C5A880]' : 'ds-badge-neutral';
                                        @endphp
                                        <span class="ds-badge capitalize {{ $roleBadgeClass }}">{{ str_replace('_', ' ', $role) }}</span>

                                        @if(auth()->user()->tenant)
                                            <span class="ds-badge ds-badge-neutral max-w-[150px] truncate" title="{{ auth()->user()->tenant->name }}">{{ auth()->user()->tenant->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(!auth()->user()->isMember() && auth()->user()->tenant && auth()->user()->tenant->activeSubscription)
                                <div class="mt-3 pt-3 border-t border-line flex items-center justify-between text-xs">
                                    <span class="text-fg-muted">Current plan</span>
                                    <span class="font-medium text-fg">{{ auth()->user()->tenant->activeSubscription->plan?->name ?? 'Standard' }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Shortcuts -->
                        <div class="py-1.5">
                            @if(!$authUser?->isMember() && !$isPureTrainer)
                                @if(auth()->user()->isSuperAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                        <svg class="w-4 h-4 text-fg-subtle shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Super Admin Portal</span>
                                    </a>
                                @endif

                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isGymOwner())
                                    <a href="{{ route('app.settings.index') }}" 
                                       class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Gym settings</span>
                                    </a>

                                    <a href="{{ route('app.subscription.index') }}" 
                                       class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span>Subscription &amp; billing</span>
                                    </a>

                                    <a href="{{ route('app.roles.index') }}" 
                                       class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Roles &amp; permissions</span>
                                    </a>
                                @endif

                                @if(auth()->user()->hasFeature('whatsapp_marketing') && auth()->user()->hasPermission('whatsapp.view'))
                                <a href="{{ route('app.whatsapp.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                    <svg class="w-4 h-4 text-fg-subtle shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                                @endif
                            @endif

                            @if(auth()->user()->isMember())
                                <a href="{{ route('app.feedback.index') }}" 
                                   class="flex items-center justify-between px-4 py-2.5 text-neutral-300 hover:text-white hover:bg-neutral-800/60 transition-colors font-medium">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-[#C5A880] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span>Gym Feedback &amp; Suggestions</span>
                                    </div>
                                </a>
                            @else
                                <a href="{{ route('app.feedback.index') }}" 
                                   class="flex items-center justify-between px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-fg-subtle shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                        <span>Member feedback</span>
                                    </div>
                                    @php
                                        $newFeedbackCount = \App\Models\CrmFeedback::where('tenant_id', auth()->user()->tenant_id)->whereIn('status', ['new', 'pending', 'open'])->count();
                                    @endphp
                                    @if($newFeedbackCount > 0)
                                        <span class="ds-nav-count !ml-0">{{ $newFeedbackCount }}</span>
                                    @endif
                                </a>

                                <a href="{{ route('app.support.index') }}" 
                                   class="flex items-center gap-2.5 px-4 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised transition-colors">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <span>Platform support</span>
                                </a>
                            @endif
                        </div>

                        <!-- Account Actions (Change Password & Logout) -->
                        <div class="p-2 space-y-0.5">
                            <button @click="userMenuOpen = false; showPasswordModal = true"
                                    type="button"
                                    class="w-full flex items-center gap-2.5 px-3 h-9 text-[13px] font-medium text-fg-muted hover:text-fg hover:bg-raised rounded-lg transition-colors text-left cursor-pointer">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Change password</span>
                            </button>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2.5 px-3 h-9 text-[13px] font-medium text-danger hover:bg-danger/10 rounded-lg transition-colors text-left cursor-pointer">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Modal -->
                    <div x-show="showPasswordModal" 
                         x-cloak
                         @keydown.escape.window="showPasswordModal = false"
                         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        
                        <div @click.away="showPasswordModal = false"
                             class="w-full max-w-md {{ $isMemberUser ? 'bg-[#171A22] text-[#F4EDE4] border-neutral-800' : 'bg-surface border-line-strong' }} rounded-xl border shadow-xl overflow-hidden relative"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4 border-b {{ $isMemberUser ? 'border-neutral-800' : 'border-line' }} flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $isMemberUser ? 'bg-[#C5A880]/15 text-[#C5A880] border border-[#C5A880]/30' : 'bg-raised text-fg-muted border border-line' }} flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-fg">Change password</h3>
                                        <p class="text-xs text-fg-muted mt-0.5">Use a strong password you don't use anywhere else.</p>
                                    </div>
                                </div>
                                <button @click="showPasswordModal = false" type="button" class="text-fg-subtle hover:text-fg p-1 rounded-lg hover:bg-raised transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Form -->
                            <form action="{{ route('app.profile.change-password') }}" method="POST" class="p-6 space-y-4">
                                @csrf

                                @if ($errors->any())
                                    <div class="p-3 rounded-lg bg-danger/10 border border-danger/25 text-danger text-sm space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <p class="flex items-center gap-1.5 font-medium">
                                                <span>•</span> {{ $error }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Current Password -->
                                <div>
                                    <label class="ds-label">Current password</label>
                                    <div class="relative">
                                        <input :type="showCurrentPass ? 'text' : 'password'" 
                                               name="current_password" 
                                               required 
                                               placeholder="Enter current password"
                                               class="ds-input !pr-10">
                                        <button type="button" 
                                                @click="showCurrentPass = !showCurrentPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-fg-subtle hover:text-fg">
                                            <svg x-show="!showCurrentPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showCurrentPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label class="ds-label">New password</label>
                                    <div class="relative">
                                        <input :type="showNewPass ? 'text' : 'password'" 
                                               name="password" 
                                               required 
                                               minlength="8"
                                               placeholder="Minimum 8 characters"
                                               class="ds-input !pr-10">
                                        <button type="button" 
                                                @click="showNewPass = !showNewPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-fg-subtle hover:text-fg">
                                            <svg x-show="!showNewPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showNewPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-fg-subtle mt-1.5">At least 8 characters, different from your current password.</p>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="ds-label">Confirm new password</label>
                                    <div class="relative">
                                        <input :type="showConfirmPass ? 'text' : 'password'" 
                                               name="password_confirmation" 
                                               required 
                                               placeholder="Repeat new password"
                                               class="ds-input !pr-10">
                                        <button type="button" 
                                                @click="showConfirmPass = !showConfirmPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-fg-subtle hover:text-fg">
                                            <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showConfirmPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="pt-3 flex items-center justify-end gap-2.5">
                                    <button type="button" 
                                            @click="showPasswordModal = false"
                                            class="ds-btn ds-btn-secondary">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="ds-btn ds-btn-primary">
                                        Update password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">✕</button>
            </div>
        @endif

        @if (session('warning'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs flex items-center justify-between">
                <span>{{ session('warning') }}</span>
                <button onclick="this.parentElement.remove()" class="text-amber-400 hover:text-amber-300">✕</button>
            </div>
        @endif

        <!-- Page View Body -->
        <main class="flex-1 p-3.5 sm:p-5 md:p-8 pb-28 lg:pb-8 w-full max-w-full overflow-x-hidden {{ !$isStealthApp && request()->routeIs('app.crm.enquiries', 'app.enquiries.*', 'app.members.*', 'app.agreements.*', 'app.memberships.*', 'app.membership-plans.*', 'app.attendance.*', 'app.payments.*') ? 'operations-workspace' : '' }}">
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Visible only on mobile/tablet) -->
    <nav x-data="{
            activeTab: '{{ request()->query('tab', 'overview') }}',
            trainerTab: '{{ request()->query('tab', 'today') }}',
            navigate(tabName, url) {
                this.activeTab = tabName;
                if (window.location.pathname.includes('/portal')) {
                    window.dispatchEvent(new CustomEvent('switch-portal-tab', { detail: tabName }));
                    if (window.history && window.history.replaceState) {
                        const newUrl = new URL(window.location);
                        if (tabName === 'overview') {
                            newUrl.searchParams.delete('tab');
                        } else {
                            newUrl.searchParams.set('tab', tabName);
                        }
                        window.history.replaceState(null, '', newUrl.toString());
                    }
                } else {
                    window.location.href = url;
                }
            },
            navigateTrainer(tabName, url) {
                this.trainerTab = tabName;
                if (window.location.pathname.includes('/app/trainer')) {
                    window.dispatchEvent(new CustomEvent('trainer-tab-changed', { detail: tabName }));
                    if (window.history && window.history.replaceState) {
                        const newUrl = new URL(window.location);
                        if (tabName === 'today') {
                            newUrl.searchParams.delete('tab');
                        } else {
                            newUrl.searchParams.set('tab', tabName);
                        }
                        window.history.replaceState(null, '', newUrl.toString());
                    }
                } else {
                    window.location.href = url;
                }
            }
         }"
         x-on:portal-tab-changed.window="activeTab = $event.detail"
         x-on:trainer-tab-changed.window="trainerTab = $event.detail"
         class="fixed bottom-0 inset-x-0 z-40 {{ $isStealthApp ? ($isMemberUser ? 'bg-[#0F1115]/95 border-t border-neutral-800' : 'lg:hidden bg-[#0F1115]/95 border-t border-neutral-800') : 'lg:hidden bg-surface border-t border-line' }} px-1 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))]"
         style="position: fixed !important; bottom: 0 !important; left: 0 !important; right: 0 !important; z-index: 40 !important;">
        <div class="grid {{ $isPureTrainer ? 'grid-cols-4' : 'grid-cols-5' }} items-center gap-1 max-w-lg mx-auto">
            @if(auth()->user()->isMember())
                <!-- Member Fitness App Navigation (Option 2 Luxury Studio) -->
                <!-- 1. Home -->
                <button type="button" 
                        @click="navigate('overview', '{{ route('app.portal') }}')"
                        :class="activeTab === 'overview' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span x-show="activeTab === 'overview'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Home</span>
                </button>

                <!-- 2. Workouts -->
                <button type="button" 
                        @click="navigate('workouts', '{{ route('app.portal', ['tab' => 'workouts']) }}')"
                        :class="activeTab === 'workouts' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span x-show="activeTab === 'workouts'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Workout</span>
                </button>

                <!-- 3. Diet -->
                <button type="button" 
                        @click="navigate('diets', '{{ route('app.portal', ['tab' => 'diets']) }}')"
                        :class="activeTab === 'diets' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span x-show="activeTab === 'diets'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Diet</span>
                </button>

                <!-- 4. PT / Coach (Personal Training) -->
                <button type="button" 
                        @click="navigate('pt', '{{ route('app.portal', ['tab' => 'pt']) }}')"
                        :class="activeTab === 'pt' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m-9-9h2m14 0h2"/></svg>
                        <span x-show="activeTab === 'pt'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Coach PT</span>
                </button>

                <!-- 5. Pass & Visits -->
                <button type="button" 
                        @click="navigate('memberships', '{{ route('app.portal', ['tab' => 'memberships']) }}')"
                        :class="activeTab === 'memberships' || activeTab === 'attendance' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span x-show="activeTab === 'memberships' || activeTab === 'attendance'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Pass</span>
                </button>
            @elseif($isPureTrainer)
                <!-- Trainer App Mobile Navigation (Champagne Stealth) -->
                <!-- 1. Today -->
                <button type="button" 
                        @click="navigateTrainer('today', '{{ route('trainer.workspace', ['tab' => 'today']) }}')"
                        :class="trainerTab === 'today' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-show="trainerTab === 'today'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Today</span>
                </button>

                <!-- 2. Members -->
                <button type="button" 
                        @click="navigateTrainer('members', '{{ route('trainer.workspace', ['tab' => 'members']) }}')"
                        :class="trainerTab === 'members' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span x-show="trainerTab === 'members'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Members</span>
                </button>

                <!-- 3. PT Sessions -->
                <button type="button" 
                        @click="navigateTrainer('sessions', '{{ route('trainer.workspace', ['tab' => 'sessions']) }}')"
                        :class="trainerTab === 'sessions' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span x-show="trainerTab === 'sessions'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Sessions</span>
                </button>

                <!-- 4. Profile -->
                <button type="button" 
                        @click="navigateTrainer('profile', '{{ route('trainer.workspace', ['tab' => 'profile']) }}')"
                        :class="trainerTab === 'profile' ? 'text-[#C5A880] font-black' : 'text-neutral-400 hover:text-white'"
                        class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all cursor-pointer">
                    <div class="relative p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span x-show="trainerTab === 'profile'" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-0.5 truncate">Profile</span>
                </button>
            @else
                <!-- Admin / owner / staff navigation -->
                <a href="{{ route('app.dashboard') }}"
                   @if(request()->routeIs('app.dashboard')) aria-current="page" @endif
                   class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('app.dashboard') ? 'text-accent-fg font-semibold' : 'text-fg-muted hover:text-fg' }}">
                    <x-icon name="home" :size="20" />
                    <span class="truncate">Home</span>
                </a>

                <a href="{{ route('app.members.index') }}"
                   @if(request()->routeIs('app.members.*')) aria-current="page" @endif
                   class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('app.members.*') ? 'text-accent-fg font-semibold' : 'text-fg-muted hover:text-fg' }}">
                    <x-icon name="users" :size="20" />
                    <span class="truncate">Members</span>
                </a>

                <a href="{{ route('app.attendance.index') }}"
                   @if(request()->routeIs('app.attendance.*')) aria-current="page" @endif
                   class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('app.attendance.*') ? 'text-accent-fg font-semibold' : 'text-fg-muted hover:text-fg' }}">
                    <x-icon name="clock" :size="20" />
                    <span class="truncate">Attendance</span>
                </a>

                <a href="{{ route('app.whatsapp.inbox') }}"
                   @if(request()->routeIs('app.whatsapp.*')) aria-current="page" @endif
                   class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('app.whatsapp.*') ? 'text-accent-fg font-semibold' : 'text-fg-muted hover:text-fg' }}">
                    <x-icon name="message" :size="20" />
                    <span class="truncate">WhatsApp</span>
                </a>

                <button type="button" @click="sidebarOpen = true"
                        class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-lg text-xs text-fg-muted hover:text-fg transition-colors cursor-pointer">
                    <x-icon name="menu" :size="20" />
                    <span>Menu</span>
                </button>
            @endif
        </div>
    </nav>

    <x-confirm-modal />

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif

    <script>
        function notificationCenter() {
            return {
                isOpen: false,
                isLoading: false,
                activeFilter: 'all',
                notifications: [],
                unreadCount: 0,
                counts: { all: 0, whatsapp: 0, renewal: 0, payment: 0, birthday: 0, lead: 0 },
                feedInterval: null,

                init() {
                    this.isOpen = false;
                    this.fetchFeed();
                    // Auto-refresh notifications periodically every 45 seconds
                    this.feedInterval = setInterval(() => {
                        this.fetchFeed(true);
                    }, 45000);
                },

                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.fetchFeed();
                    }
                },

                async fetchFeed(silent = false) {
                    if (!silent) this.isLoading = true;
                    try {
                        const res = await fetch("{{ route('app.notifications.feed') }}", {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.success) {
                                this.notifications = data.notifications || [];
                                this.unreadCount = data.unread_count || 0;
                                this.counts = data.counts || this.counts;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to load notifications", err);
                    } finally {
                        if (!silent) this.isLoading = false;
                    }
                },

                filteredNotifications() {
                    if (this.activeFilter === 'all') {
                        return this.notifications;
                    }
                    return this.notifications.filter(item => item.type === this.activeFilter);
                },

                async markAsRead(id) {
                    const item = this.notifications.find(n => n.id === id);
                    if (item && !item.is_read) {
                        item.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch("{{ route('app.notifications.mark-read') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ id: id })
                        });
                    } catch (err) {
                        console.error("Failed to mark notification read", err);
                    }
                },

                async markAllAsRead() {
                    const allIds = this.notifications.map(n => n.id);
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch("{{ route('app.notifications.mark-all-read') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ ids: allIds })
                        });
                    } catch (err) {
                        console.error("Failed to mark all notifications read", err);
                    }
                }
            };
        }
    </script>

    @stack('scripts')
</body>
</html>

