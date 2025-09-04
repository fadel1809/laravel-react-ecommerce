<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Auth;
use Cookie;
use DB;
use Log;

class CartService
{
    // Cache for cart items (to avoid repeated DB/cookie queries)
    private ?array $cachedCartItems = null;

    // Cookie settings
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365; // 1 year

    /**
     * Add a product to the cart.
     * - If optionIds are not provided, default to the first option of each variation type.
     * - Save to database if user is logged in, otherwise save to cookies.
     */
    public function addItemToCart(Product $product, int $quantity = 1, $optionIds = null)
    {
        if ($optionIds === null) {
            $optionIds = $product->variationTypes
                ->mapWithKeys(fn(VariationType $type) => [$type->id => $type->options[0]?->id])
                ->toArray();
        }

        $price = $product->getPriceForOptions($optionIds);

        if (Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    /**
     * Update the quantity of an existing cart item.
     */
    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        if (Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    /**
     * Remove a cart item (by product + variation options).
     */
    public function removeItemFromCart(int $productId, ?array $optionIds): void
    {
        if (Auth::check()) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    /**
     * Get all cart items with product and option details.
     * Handles both DB and cookie storage.
     */
    public function getCartItems(): array
    {
        try {
            if ($this->cachedCartItems === null) {
                // Load cart items from DB or cookies depending on login status
                $cartItems = Auth::check()
                    ? $this->getCartItemsFromDatabase()
                    : $this->getCartItemsFromCookies();

                if (!is_array($cartItems)) {
                    $cartItems = [];
                }

                // Convert collection of objects to array if needed
                if (!empty($cartItems) && is_object(current($cartItems))) {
                    $cartItems = collect($cartItems)->toArray();
                }

                // Collect product IDs from cart
                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];
                foreach ($cartItems as $cartItem) {
                    if (!is_array($cartItem)) {
                        continue;
                    }

                    $product = data_get($products, $cartItem['product_id']);
                    if (!$product) {
                        continue;
                    }

                    $optionInfo = [];
                    $imageUrl = null;

                    // Load option details (like color, size, etc.)
                    if (!empty($cartItem['option_ids'])) {
                        $options = VariationTypeOption::with('variationType')
                            ->whereIn('id', $cartItem['option_ids'])
                            ->get()
                            ->keyBy('id');

                        foreach ($cartItem['option_ids'] as $option_id) {
                            $option = data_get($options, $option_id);
                            if (!$option) continue;

                            if (!$imageUrl) {
                                $imageUrl = $option->getFirstMediaUrl('images', 'small');
                            }

                            $optionInfo[] = [
                                'id' => $option_id,
                                'name' => $option->name,
                                'type' => [
                                    'id' => $option->variationType->id,
                                    'name' => $option->variationType->name
                                ]
                            ];
                        }
                    }

                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => floatval($cartItem['price']),
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'],
                        'options' => $optionInfo,
                        'image' => $imageUrl ?? $product->getFirstMediaUrl('images', 'small'),
                        'user' => [
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name,
                        ]
                    ];
                }

                $this->cachedCartItems = $cartItemData;
            }
            return $this->cachedCartItems;
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return [];
    }

    /**
     * Get total quantity of items in cart.
     */
    public function getTotalQuantity(): int
    {
        $totalQuantity = 0;
        foreach ($this->getCartItems() as $item) {
            $totalQuantity += $item['quantity'];
        }
        return $totalQuantity;
    }

    /**
     * Get total price of items in cart.
     */
    public function getTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ($this->getCartItems() as $item) {
            $totalPrice += $item['quantity'] * $item['price'];
        }
        return $totalPrice;
    }

    /**
     * Update item quantity in database.
     */
    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds): void
    {
        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $quantity]);
        }
    }

    /**
     * Update item quantity in cookies.
     */
    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionIds);
        $itemKey = $productId . '_' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    /**
     * Save item to database (or update if exists).
     */
    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds): void
    {
        $userId = Auth::id();
        ksort($optionIds);

        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => DB::raw('quantity + ' . $quantity)
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds
            ]);
        }
    }

    /**
     * Save item to cookies.
     */
    protected function saveItemToCookies(int $productId, int $quantity, $price, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionIds);

        $itemKey = $productId . '_' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price;
        } else {
            $cartItems[$itemKey] = [
                'id' => \Str::uuid()->toString(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds
            ];
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    /**
     * Remove item from database.
     */
    protected function removeItemFromDatabase(int $productId, array $optionIds): void
    {
        $userId = Auth::id();
        ksort($optionIds);

        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->delete();
    }

    /**
     * Remove item from cookies.
     */
    protected function removeItemFromCookies(int $productId, ?array $optionIds = null): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionIds);

        $cartKey = $productId . '_' . json_encode($optionIds);
        unset($cartItems[$cartKey]);

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    /**
     * Get all cart items from database (for logged-in users).
     */
    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();

        return CartItem::where('user_id', $userId)->get()
            ->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'option_ids' => $cartItem->variation_type_option_ids
                ];
            })->toArray();
    }

    /**
     * Get all cart items from cookies (for guest users).
     */
    protected function getCartItemsFromCookies()
    {
        return json_decode(Cookie::get(self::COOKIE_NAME, "[]"), true);
    }

    /**
     * Get cart items grouped by seller (vendor).
     * Useful for multi-vendor checkout.
     */
    public function getCartItemsGrouped()
    {
        $cartItems = $this->getCartItems();

        return collect($cartItems)->groupBy(fn($item) => $item['user']['id'])
            ->map(fn($items, $userId) => [
                'user' => $items->first()['user'],
                'items' => $items->toArray(),
                'totalQuantity' => $items->sum('quantity'),
                'totalPrice' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
            ])
            ->toArray();
    }

    /**
     * Move guest cart items from cookies into database after login.
     */
    public function moveCartItemsToDatabase($userId): void
    {
        $cartItems = $this->getCartItemsFromCookies();

        foreach ($cartItems as $cartItem) {
            $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $cartItem['product_id'])
                ->where('variation_type_option_ids', json_encode($cartItem['option_ids']))
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $cartItem['quantity'],
                    'price' => $cartItem['price']
                ]);
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'variation_type_option_ids' => $cartItem['option_ids']
                ]);
            }
        }

        // Clear cart cookies after migration
        Cookie::queue(self::COOKIE_NAME, '', -1);
    }
}
