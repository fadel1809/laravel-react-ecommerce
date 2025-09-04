<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Guest Routes (no authentication required)
|--------------------------------------------------------------------------
| - Homepage (dashboard)
| - Product detail page (by slug)
*/
Route::get('/', [ProductController::class,'home'])->name('dashboard'); 
// GET / → ProductController@home → show homepage with product listing

Route::get('/products/{product:slug}', [ProductController::class,'show'])->name('product.show'); 
// GET /products/{slug} → ProductController@show → show product detail page


/*
|--------------------------------------------------------------------------
| Cart Routes (shopping cart)
|--------------------------------------------------------------------------
| - View cart
| - Add product to cart
| - Update product quantity in cart
| - Remove product from cart
*/
Route::controller(CartController::class)->group(function() {
    Route::get('/cart', 'index')->name('cart.index'); 
    // GET /cart → CartController@index → show current cart contents

    Route::post('/cart/add/{product}', 'store')->name('cart.store'); 
    // POST /cart/add/{product_id} → CartController@store → add product to cart

    Route::put('/cart/{product}', 'update')->name('cart.update'); 
    // PUT /cart/{product_id} → CartController@update → update product quantity in cart

    Route::delete('/cart/{product}', 'destroy')->name('cart.destroy'); 
    // DELETE /cart/{product_id} → CartController@destroy → remove product from cart
});


/*
|--------------------------------------------------------------------------
| Authenticated Routes (login required)
|--------------------------------------------------------------------------
| - Manage user profile
| - Checkout (requires verified email)
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); 
    // GET /profile → ProfileController@edit → show profile edit form

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); 
    // PATCH /profile → ProfileController@update → update profile information

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); 
    // DELETE /profile → ProfileController@destroy → delete user account

    Route::middleware(['verified'])->group(function() {
        Route::post('/cart/checkout', [CartController::class,'checkout'])->name('cart.checkout'); 
        // POST /cart/checkout → CartController@checkout → process checkout (only for verified users)
    });  
});


/*
|--------------------------------------------------------------------------
| Authentication scaffolding (Laravel Breeze / Jetstream)
|--------------------------------------------------------------------------
| - Includes routes for login, register, password reset, etc.
*/
require __DIR__.'/auth.php';
