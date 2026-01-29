<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shopify;

use App\Contracts\Shopify\ShopifyOAuthServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OAuthController extends Controller
{
    public function __construct(
        private readonly ShopifyOAuthServiceInterface $oauthService
    ) {}

    public function install(Request $request): RedirectResponse|View
    {
        $shop = $request->query('shop');

        if (!$shop) {
            return view('shopify.install', ['error' => null]);
        }

        if (!$this->isValidShopDomain($shop)) {
            return view('shopify.install', ['error' => 'Invalid shop domain format.']);
        }

        $existingStore = $this->oauthService->getStoreByDomain($shop);
        if ($existingStore && $existingStore->hasValidCredentials()) {
            return redirect()->route('shopify.oauth.success')->with('shop', $existingStore->shop_domain);
        }

        return redirect()->away($this->oauthService->getAuthorizationUrl($shop));
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $shop = $request->query('shop');
        $code = $request->query('code');
        $state = $request->query('state');
        $hmac = $request->query('hmac');

        if (!$shop || !$code || !$hmac) {
            return view('shopify.install', ['error' => 'Missing required parameters.']);
        }

        if (!$this->oauthService->validateHmac($request->query())) {
            return view('shopify.install', ['error' => 'Invalid request signature.']);
        }

        if ($state && !$this->oauthService->validateState($state)) {
            return view('shopify.install', ['error' => 'Session expired. Please try again.']);
        }

        try {
            $store = $this->oauthService->exchangeCodeForToken($shop, $code);
            return redirect()->route('shopify.oauth.success')
                ->with('shop', $store->shop_domain)
                ->with('message', 'Successfully connected!');
        } catch (\Exception $e) {
            return view('shopify.install', ['error' => 'Failed to connect. Please try again.']);
        }
    }

    public function success(Request $request): View
    {
        return view('shopify.success', [
            'shop' => session('shop'),
            'message' => session('message', 'Store connected successfully!'),
        ]);
    }

    private function isValidShopDomain(string $domain): bool
    {
        $domain = preg_replace('#^https?://#', '', $domain);
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $domain);
    }
}
