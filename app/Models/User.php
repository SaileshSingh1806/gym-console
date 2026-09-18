<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\TenantContext;
use Database\Factories\UserFactory;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'avatar_url',
        'role',
        'status',
        'password',
        'metadata',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'metadata' => 'array',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function currentBranch(): ?Branch
    {
        return TenantContext::getBranch()
            ?? $this->tenant?->mainBranch
            ?? $this->tenant?->branches()->first();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function trainerProfile(): HasOne
    {
        return $this->hasOne(Trainer::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isGymOwner(): bool
    {
        return $this->role === 'gym_owner';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['gym_owner', 'gym_manager']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['gym_owner', 'gym_manager', 'receptionist', 'trainer', 'accountant', 'staff'])
            || ($this->tenant_id && Role::where('tenant_id', $this->tenant_id)->where('name', $this->role)->exists());
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Feature gate check: if the permission belongs to a feature not in tenant's SaaS plan, deny
        $featureMap = PermissionSeeder::getPermissionFeatureMap();
        $featureCode = $featureMap[$permission] ?? null;
        if ($featureCode !== null && ! $this->hasFeature($featureCode)) {
            return false;
        }

        if ($this->isGymOwner()) {
            return true;
        }

        if ($this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists()) {
            return true;
        }

        if (! empty($this->role) && $this->tenant_id) {
            $roleObj = Role::where('tenant_id', $this->tenant_id)
                ->where('name', $this->role)
                ->first();

            if ($roleObj && $roleObj->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin() || $this->isGymOwner()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasFeature(string $featureCode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->tenant_id) {
            return false;
        }

        $tenant = $this->tenant ?? Tenant::find($this->tenant_id);

        return $tenant ? $tenant->hasFeature($featureCode) : false;
    }

    /**
     * @param  array<int, string>  $featureCodes
     */
    public function hasAnyFeature(array $featureCodes): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($featureCodes as $code) {
            if ($this->hasFeature($code)) {
                return true;
            }
        }

        return false;
    }
}
