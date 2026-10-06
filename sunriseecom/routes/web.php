<?php

use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\CartController as AdminCartController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\WishlistController as AdminWishlistController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/search', SearchController::class)->name('search');
Route::get('/about', fn () => app(PageController::class)->show('about'))->name('about');
Route::get('/careers', fn () => app(PageController::class)->show('careers'))->name('careers');
Route::get('/press', fn () => app(PageController::class)->show('press'))->name('press');
Route::get('/support', [SupportController::class, 'create'])->name('support');
Route::post('/support', [SupportController::class, 'store'])->name('support.store');
Route::get('/packages/{plan}', [PlanController::class, 'show'])->name('packages.show');
Route::get('/privacy', fn () => app(PageController::class)->show('privacy'))->name('privacy');
Route::get('/terms', fn () => app(PageController::class)->show('terms'))->name('terms');
Route::get('/refund', fn () => app(PageController::class)->show('refund'))->name('refund');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{service}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/quantity', [CartController::class, 'quantity'])->name('cart.quantity');
Route::post('/cart/save', [CartController::class, 'save'])->name('cart.save');
Route::post('/cart/move', [CartController::class, 'move'])->name('cart.move');
Route::post('/cart/saved', [CartController::class, 'forgetSaved'])->name('cart.saved.destroy');
Route::post('/cart/coupon/remove', [CartController::class, 'forgetCoupon'])->name('cart.coupon.remove');
Route::post('/cart/coupon', [CartController::class, 'coupon'])->name('cart.coupon');
Route::post('/cart/{service}', [CartController::class, 'store'])->name('cart.store');
Route::post('/cart', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/services/{service}/buy', [CheckoutController::class, 'buy'])->name('checkout.buy');
Route::post('/plans/{plan}', [PlanController::class, 'subscribe'])->name('plans.subscribe');
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/services/{service}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/packages/{plan}/reviews', [ReviewController::class, 'storePackage'])->name('packages.reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{service}/quote', [QuoteController::class, 'create'])->name('quotes.create');
Route::post('/services/{service}/quote', [QuoteController::class, 'store'])->name('quotes.store');
Route::get('/deals', function () {
    return view('store.deals');
})->name('deals');
Route::get('/best-sellers', function () {
    return view('store.best-sellers');
})->name('best-sellers');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/payments/razorpay/webhook', [CheckoutController::class, 'webhook'])->name('payments.razorpay.webhook');

Route::post('/login', [LoginController::class, 'store']);

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');
});

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminLoginController::class, 'home'])->name('home');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::post('/categories/{category}/hide', [AdminCategoryController::class, 'hide'])->name('categories.hide');
    Route::post('/categories/{category}/restore', [AdminCategoryController::class, 'restore'])->name('categories.restore');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);

    Route::post('/services/{service}/hide', [AdminServiceController::class, 'hide'])->name('services.hide');
    Route::post('/services/{service}/restore', [AdminServiceController::class, 'restore'])->name('services.restore');
    Route::resource('services', AdminServiceController::class)->except(['show']);

    Route::post('/packages/{package}/hide', [AdminPackageController::class, 'hide'])->name('packages.hide');
    Route::post('/packages/{package}/restore', [AdminPackageController::class, 'restore'])->name('packages.restore');
    Route::resource('packages', AdminPackageController::class)->except(['show']);

    Route::resource('quotes', AdminQuoteController::class)->only(['index', 'edit', 'update', 'destroy']);

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/hide', [AdminReviewController::class, 'hide'])->name('reviews.hide');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');

    Route::get('/business', [AdminBusinessController::class, 'edit'])->name('business.edit');
    Route::put('/business', [AdminBusinessController::class, 'update'])->name('business.update');

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);

    Route::get('/carts', [AdminCartController::class, 'index'])->name('carts.index');
    Route::get('/wishlists', [AdminWishlistController::class, 'index'])->name('wishlists.index');

    Route::post('/coupons/{coupon}/hide', [AdminCouponController::class, 'hide'])->name('coupons.hide');
    Route::post('/coupons/{coupon}/restore', [AdminCouponController::class, 'restore'])->name('coupons.restore');
    Route::resource('coupons', AdminCouponController::class)->except(['show']);
});
