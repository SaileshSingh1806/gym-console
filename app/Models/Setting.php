<?php

namespace App\Models;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function get(string $key, $default = null, $tenantId = false)
    {
        $resolvedTenantId = $tenantId !== false
            ? $tenantId
            : (! TenantContext::isBypassed() ? TenantContext::tenantId() : null);

        $query = static::where('key', $key);
        if ($resolvedTenantId === null) {
            $query->whereNull('tenant_id');
        } else {
            $query->where('tenant_id', $resolvedTenantId);
        }

        $setting = $query->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'json' => json_decode($setting->value, true),
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            default => $setting->value,
        };
    }

    public static function set(string $key, $value, string $type = 'string', $tenantId = false): self
    {
        $resolvedTenantId = $tenantId !== false
            ? $tenantId
            : (! TenantContext::isBypassed() ? TenantContext::tenantId() : null);

        $val = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key, 'tenant_id' => $resolvedTenantId],
            ['value' => $val, 'type' => $type]
        );
    }

    public static function getGlobal(string $key, $default = null)
    {
        $setting = static::where('key', $key)->whereNull('tenant_id')->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'json' => json_decode($setting->value, true),
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            default => $setting->value,
        };
    }

    public static function setGlobal(string $key, $value, string $type = 'string'): self
    {
        $val = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key, 'tenant_id' => null],
            ['value' => $val, 'type' => $type]
        );
    }

    public static function getAllGlobal(): array
    {
        $rows = static::whereNull('tenant_id')->get();
        $results = [];

        foreach ($rows as $row) {
            $results[$row->key] = match ($row->type) {
                'json' => json_decode($row->value, true),
                'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $row->value,
                default => $row->value,
            };
        }

        return $results;
    }

    public static function setManyGlobal(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $type = 'string';
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_int($value)) {
                $type = 'integer';
            } elseif (is_array($value)) {
                $type = 'json';
            }

            static::setGlobal($key, $value, $type);
        }
    }
}
