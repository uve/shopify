<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'image',
        'images',
        'is_active',
        'shopify_product_id',
        'shopify_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'images' => 'array',
            'is_active' => 'boolean',
            'shopify_synced_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function decrementStock(int $quantity): void
    {
        if ($this->stock_quantity < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient stock. Available: {$this->stock_quantity}, requested: {$quantity}"
            );
        }

        $this->decrement('stock_quantity', $quantity);
    }

    public function isSyncedWithShopify(): bool
    {
        return $this->shopify_product_id !== null;
    }
}
