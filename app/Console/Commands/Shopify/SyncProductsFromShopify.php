<?php

declare(strict_types=1);

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyStore;
use App\Services\Shopify\ProductSyncService;
use App\Services\Shopify\ShopifyApiClient;
use Illuminate\Console\Command;

class SyncProductsFromShopify extends Command
{
    protected $signature = 'shopify:sync-from';
    protected $description = 'Sync products from Shopify to local database';

    public function handle(): int
    {
        $this->info('Starting sync from Shopify...');

        $store = ShopifyStore::where('is_active', true)->first();

        if (!$store || !$store->access_token) {
            $this->error('No connected store. Connect via OAuth first.');
            return self::FAILURE;
        }

        $this->info("Store: {$store->shop_domain}");

        $client = new ShopifyApiClient($store->shop_domain, null, $store->access_token);
        $service = new ProductSyncService($client);

        $result = $service->syncFromShopify();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Created', $result['created']],
                ['Updated', $result['updated']],
                ['Failed', $result['failed']],
            ]
        );

        $this->info('Done!');
        return self::SUCCESS;
    }
}
