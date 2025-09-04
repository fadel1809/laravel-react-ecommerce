<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Home page (dashboard).
     *
     * - Fetch products from the database using the custom scope `forWebsite()`.
     *   (This scope usually filters products that are active/visible on the website).
     * - Paginate results: 12 products per page.
     * - Render the Inertia component `Home` and pass the products data.
     * - Products are transformed with `ProductListResource` to keep the API response clean.
     */
    public function home()
    {
        $products = Product::query()
            ->forWebsite()        // custom scope: filter products for the website
            ->paginate(12);       // 12 products per page

        return Inertia::render('Home', [
            'products' => ProductListResource::collection($products)
        ]);
    }

    /**
     * Product detail page.
     *
     * - The route `/products/{product:slug}` automatically resolves
     *   the product model by slug instead of ID.
     * - Render the Inertia component `Product/Show`.
     * - Pass the following props:
     *    - `product`: detailed product data, transformed with `ProductResource`.
     *    - `variationOptions`: product variation options (color, size, etc.).
     *      These are read from the query string (?options[color]=red&options[size]=M).
     */
    public function show(Product $product)
    {
        return Inertia::render('Product/Show', [
            'product' => new ProductResource($product),
            'variationOptions' => request('options', [])
        ]);
    }
}
