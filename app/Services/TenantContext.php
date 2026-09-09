<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Tenant;

class TenantContext
{
    protected static ?Tenant $tenant = null;

    protected static ?Branch $branch = null;

    protected static bool $bypass = false;

    public static function setTenant(?Tenant $tenant): void
    {
        static::$tenant = $tenant;
        if ($tenant !== null) {
            static::$bypass = false;
        }
    }

    public static function getTenant(): ?Tenant
    {
        return static::$tenant;
    }

    public static function tenantId(): ?int
    {
        return static::$tenant?->id;
    }

    public static function setBranch(?Branch $branch): void
    {
        static::$branch = $branch;
    }

    public static function getBranch(): ?Branch
    {
        return static::$branch;
    }

    public static function branchId(): ?int
    {
        return static::$branch?->id;
    }

    public static function setBypass(bool $bypass = true): void
    {
        static::$bypass = $bypass;
    }

    public static function isBypassed(): bool
    {
        return static::$bypass;
    }

    public static function reset(): void
    {
        static::$tenant = null;
        static::$branch = null;
        static::$bypass = false;
    }
}
