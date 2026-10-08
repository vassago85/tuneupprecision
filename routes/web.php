<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\RangeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TestimonialController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\RifleBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses');

Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/book/{event}', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/book/{event}', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/confirmed', [BookingController::class, 'confirmed'])->name('bookings.confirmed');

Route::get('/calendar', CalendarController::class)->name('calendar');

// Rifle Builder is admin-only for now — Dirk is still curating components.
// Kept as a real public URL (rather than moving into /admin) so the existing
// Livewire component + layout stays untouched; the guard just fires 403 for
// anyone who isn't the admin. Once Dirk is happy, drop the middleware group
// and put the nav links back.
Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/rifle-builder', RifleBuilder::class)->name('rifle-builder');
    Route::get('/rifle-builder/{code}', RifleBuilder::class)->name('rifle-builder.share');
});

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/shop/checkout', [ShopController::class, 'place'])->name('shop.checkout.place');
Route::get('/shop/order/confirmation', [ShopController::class, 'confirmation'])->name('shop.confirmation');
Route::post('/shop/cart', [ShopController::class, 'add'])->name('shop.cart.add');
Route::patch('/shop/cart/{product}', [ShopController::class, 'update'])->name('shop.cart.update');
Route::delete('/shop/cart/{product}', [ShopController::class, 'remove'])->name('shop.cart.remove');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/the-range', RangeController::class)->name('range');

// Public authentication (member accounts). Admins (Dirk) also log in here —
// they get redirected to /admin on success.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/password/forgot', [PasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/password/forgot', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/password/reset/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('contact.store');

// Testimonials — a stable public link (shared from the admin dashboard) plus
// the signed invite emails, which still pre-fill name and event. Every
// submission is queued for admin approval before appearing on the site.
Route::get('/testimonials/submit', [TestimonialController::class, 'create'])
    ->name('testimonials.create');
Route::post('/testimonials', [TestimonialController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('testimonials.store');
Route::get('/testimonials/thanks', [TestimonialController::class, 'thanks'])
    ->name('testimonials.thanks');

Route::view('/legal', 'legal.index')->name('legal.index');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/shipping', 'legal.shipping')->name('legal.shipping');
Route::view('/refunds', 'legal.refunds')->name('legal.refunds');

// Newsletter subscribe (public form) + one-click unsubscribe.
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

// SEO / AI discoverability — sitemap.xml for search engines, llms.txt for
// language-model crawlers (llmstxt.org).
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', [LlmsTxtController::class, 'index'])->name('llms');
Route::get('/llms-full.txt', [LlmsTxtController::class, 'full'])->name('llms.full');
