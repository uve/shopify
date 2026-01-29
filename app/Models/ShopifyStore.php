<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Shopify Store model.
 * 
 * Represents a connected Shopify store with OAuth credentials.
 *
 * @property int $id
 * @property string $shop_domain
 * @property string $access_token
 * @property string|null $scope
 * @property bool $is_active
 * @property \Carbon\Carbon|null $installed_at
 * @property \Carbon\Carbon|null $uninstalled_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ShopifyStore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_domain',
        'access_token',
        'scope',
        'is_active',
        'installed_at',
        'uninstalled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'installed_at' => 'datetime',
            'uninstalled_at' => 'datetime',
            'access_token' => 'encrypted',
        ];
    }

    /**
     * Check if the store has valid credentials.
     */
    public function hasValidCredentials(): bool
    {
        return $this->is_active 
            && $this->access_token !== null 
            && $this->access_token !== '';
    }

    /**
     * Mark the store as uninstalled.
     */
    public function markAsUninstalled(): void
    {
        $this->update([
            'is_active' => false,
            'uninstalled_at' => now(),
        ]);
    }

    /**
     * Get the API base URL for this store.
     */
    public function getApiBaseUrl(string $apiVersion = '2024-01'): string
    {
        return "https://{$this->shop_domain}/admin/api/{$apiVersion}";
    }

    /**
     * Scope to get only active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
