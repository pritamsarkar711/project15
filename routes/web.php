<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\SeoController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Frontend\AuthController as FrontendAuthController;
use App\Http\Controllers\Frontend\AuthorDashboardController;

// Public storage fallback — stream files from storage/app/public when the
// public/storage symlink isn't available (common on shared hosts where symlink()
// is disabled). Laravel's `public` disk writes uploads to storage/app/public,
// and on a properly configured server the public/storage symlink makes those
// files web-accessible at /storage/*. On Hostinger shared hosting we use this
// route as a fallback to serve them via PHP.
Route::get('/storage/{path}', function ($path) {
    $full = storage_path('app/public/' . $path);

    // Block path traversal attempts.
    $realBase = realpath(storage_path('app/public'));
    $realTarget = realpath($full);
    if ($realTarget === false || $realBase === false || !str_starts_with($realTarget, $realBase)) {
        abort(404);
    }

    if (!is_file($realTarget)) {
        abort(404);
    }

    return response()->file($realTarget, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*');

// Frontend
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', [BlogController::class, 'storeComment'])->name('blog.comment.store');
Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category.show');

// Author profile (public). Pattern enforces URL-safe usernames only,
// so it won't shadow reserved routes like /blog, /search, /page/{slug}.
Route::get('/author/{username}', [BlogController::class, 'authorProfile'])
    ->where('username', '[a-z0-9._-]+')
    ->name('author.profile');

// Follow / unfollow an author (only logged-in users).
Route::post('/author/{username}/follow', [BlogController::class, 'follow'])
    ->where('username', '[a-z0-9._-]+')
    ->name('author.follow')
    ->middleware('auth');

Route::get('/about', [PageController::class,'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Static pages
Route::get('/privacy-policy', [PageController::class,'privacy'])->name('privacy');
Route::get('/terms-conditions', [PageController::class,'terms'])->name('terms');
Route::get('/cookie-policy', [PageController::class,'cookie'])->name('cookie');
Route::get('/editorial-policy', [PageController::class,'editorial'])->name('editorial');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Frontend user auth (separate from /manage admin login)
Route::get('/register', [FrontendAuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [FrontendAuthController::class, 'register'])->name('register.post')->middleware('guest');
Route::get('/login', [FrontendAuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [FrontendAuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Password reset (frontend users — uses Huvanti-branded ResetPassword
// notification that points to /reset-password/{token} instead of /manage).
Route::get('/forgot-password', [FrontendAuthController::class, 'showForgotPasswordForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [FrontendAuthController::class, 'sendResetLink'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [FrontendAuthController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [FrontendAuthController::class, 'reset'])->name('password.update')->middleware('guest');

// Author dashboard — for registered users (authors)
Route::prefix('author-dashboard')->name('author.')->middleware('auth')->group(function () {
    Route::get('/', [AuthorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/posts', [AuthorDashboardController::class, 'postsIndex'])->name('posts.index');
    Route::get('/posts/create', [AuthorDashboardController::class, 'postsCreate'])->name('posts.create');
    Route::post('/posts', [AuthorDashboardController::class, 'postsStore'])->name('posts.store');
    Route::get('/posts/{id}/edit', [AuthorDashboardController::class, 'postsEdit'])->name('posts.edit');
    Route::post('/posts/{id}', [AuthorDashboardController::class, 'postsUpdate'])->name('posts.update');
    Route::post('/posts/{id}/submit', [AuthorDashboardController::class, 'postsSubmit'])->name('posts.submit');
    Route::delete('/posts/{id}', [AuthorDashboardController::class, 'postsDestroy'])->name('posts.destroy');
    Route::get('/profile', [AuthorDashboardController::class, 'profileEdit'])->name('profile.edit');
    Route::post('/profile', [AuthorDashboardController::class, 'profileUpdate'])->name('profile.update');
    Route::get('/monetization', [AuthorDashboardController::class, 'monetization'])->name('monetization');
    Route::get('/posting-rules', [AuthorDashboardController::class, 'rules'])->name('rules');
    Route::post('/account', [AuthorDashboardController::class, 'accountDelete'])->name('account.delete');
});

// SEO / dynamic text+xml endpoints (settings-driven)
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/ads.txt', [SeoController::class, 'ads'])->name('seo.ads');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('seo.llms');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

// Admin Auth - slug is /manage for security (no Admin link on frontend)
Route::prefix('manage')->name('admin.')->group(function(){
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['admin'])->group(function(){
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Posts
        Route::resource('posts', PostController::class);
        Route::post('posts/{post}/toggle', [PostController::class,'toggleStatus'])->name('posts.toggle');
        Route::post('posts/{post}/restore', [PostController::class,'restore'])->name('posts.restore');
        Route::post('posts/{post}/permanent', [PostController::class,'forceDelete'])->name('posts.destroy.permanent');

        // Multi-author review queue (pending submissions)
        Route::get('posts/review-queue', [PostController::class, 'reviewQueue'])->name('posts.review-queue');
        Route::post('posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
        Route::post('posts/{post}/return', [PostController::class, 'return'])->name('posts.return');

        // Categories
        Route::get('categories', [CategoryController::class,'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class,'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class,'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class,'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class,'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class,'destroy'])->name('categories.destroy');
        Route::post('categories/reorder', [CategoryController::class,'reorder'])->name('categories.reorder');

        // Pages
        Route::resource('pages', AdminPageController::class);

        // Ads
        Route::get('advertisements', [AdvertisementController::class,'index'])->name('ads.index');
        Route::post('advertisements', [AdvertisementController::class,'store'])->name('ads.store');
        Route::put('advertisements/{advertisement}', [AdvertisementController::class,'update'])->name('ads.update');
        Route::delete('advertisements/{advertisement}', [AdvertisementController::class,'destroy'])->name('ads.destroy');
        Route::post('advertisements/{advertisement}/toggle', [AdvertisementController::class,'toggle'])->name('ads.toggle');

        // Contacts
        Route::get('contacts', [AdminContactController::class,'index'])->name('contacts.index');
        Route::get('contacts/{contact}', [AdminContactController::class,'show'])->name('contacts.show');
        Route::delete('contacts/{contact}', [AdminContactController::class,'destroy'])->name('contacts.destroy');
        Route::post('contacts/{contact}/read', [AdminContactController::class,'markRead'])->name('contacts.read');

        // Comments
        Route::get('comments', [AdminCommentController::class,'index'])->name('comments.index');
        Route::patch('comments/{comment}/status', [AdminCommentController::class,'updateStatus'])->name('comments.status');
        Route::delete('comments/{comment}', [AdminCommentController::class,'destroy'])->name('comments.destroy');

        // Navigation
        Route::get('navigation', [NavigationController::class,'index'])->name('navigation.index');
        Route::post('navigation', [NavigationController::class,'store'])->name('navigation.store');
        Route::put('navigation/{navigation}', [NavigationController::class,'update'])->name('navigation.update');
        Route::delete('navigation/{navigation}', [NavigationController::class,'destroy'])->name('navigation.destroy');
        Route::post('navigation/reorder', [NavigationController::class,'reorder'])->name('navigation.reorder');

        // Author profile
        Route::get('profile', [ProfileController::class,'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class,'update'])->name('profile.update');

        // Settings
        Route::get('settings', [SettingController::class,'index'])->name('settings.index');
        Route::post('settings', [SettingController::class,'update'])->name('settings.update');
        Route::post('settings/smtp', [SettingController::class,'updateSmtp'])->name('settings.smtp.update');
        Route::post('settings/test-email', [SettingController::class,'testEmail'])->name('settings.test-email');
        Route::get('settings/security', [SettingController::class,'security'])->name('settings.security');
        Route::post('settings/2fa/start', [SettingController::class,'start2FA'])->name('settings.2fa.start');
        Route::post('settings/2fa/confirm', [SettingController::class,'confirm2FA'])->name('settings.2fa.confirm');
        Route::post('settings/2fa/disable', [SettingController::class,'disable2FA'])->name('settings.2fa.disable');
    });
});
