<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ContactController;
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
use App\Http\Controllers\Admin\RoleSwitchController;
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
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*');

// Frontend
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/top-contributors', [\App\Http\Controllers\Frontend\TopContributorsController::class, 'index'])->name('top.contributors');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', [BlogController::class, 'storeComment'])->name('blog.comment.store');

// Post reactions ("Did you like this post?" like / dislike buttons).
// Logged-in users only; guests are sent to login first.
Route::post('/blog/{slug}/react', [BlogController::class, 'react'])->name('blog.react')->middleware('auth');

// Affiliate / external link click tracking (fired by JS when a visitor
// clicks an outbound link inside a post). Guests included, no auth needed.
Route::post('/blog/{slug}/click', [BlogController::class, 'trackClick'])->name('blog.click');

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
Route::get('/disclaimer', [PageController::class,'disclaimer'])->name('disclaimer');
Route::get('/affiliate-disclosure', [PageController::class,'affiliateDisclosure'])->name('affiliate');
Route::get('/comment-policy', [PageController::class,'commentPolicy'])->name('comments.policy');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Frontend user auth (separate from /manage admin login)
Route::get('/register', [FrontendAuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [FrontendAuthController::class, 'register'])->name('register.post')->middleware(['guest', 'throttle:6,1']);
Route::get('/login', [FrontendAuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [FrontendAuthController::class, 'login'])->name('login.post')->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/auth/google', [FrontendAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect')->middleware('guest');
Route::get('/auth/google/callback', [FrontendAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback')->middleware('guest');

// Admin ⇄ User switch: "switch back to admin" lives OUTSIDE /manage so it stays
// reachable while the admin is browsing the site in user mode. Only the real
// admin account can ever use it (checked in the controller).
Route::post('/switch-back-to-admin', [RoleSwitchController::class, 'switchBackToAdmin'])
    ->middleware('auth')->name('switch-back-to-admin');

// Password reset (frontend users — uses Huvanti-branded ResetPassword
// notification that points to /reset-password/{token} instead of /manage).
Route::get('/forgot-password', [FrontendAuthController::class, 'showForgotPasswordForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [FrontendAuthController::class, 'sendResetLink'])->name('password.email')->middleware(['guest', 'throttle:5,1']);
Route::get('/reset-password/{token}', [FrontendAuthController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [FrontendAuthController::class, 'reset'])->name('password.update')->middleware('guest');

// Author dashboard — for registered users (authors)
Route::prefix('author-dashboard')->name('author.')->middleware('auth')->group(function () {
    Route::get('/', [AuthorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/posts', [AuthorDashboardController::class, 'postsIndex'])->name('posts.index');
    Route::get('/posts/create', [AuthorDashboardController::class, 'postsCreate'])->name('posts.create');
    // Server-side autosave for the post editor. Registered BEFORE
    // /posts/{id} so "autosave" is never captured as {id}.
    Route::post('/posts/autosave', [AuthorDashboardController::class, 'postsAutosave'])->name('posts.autosave');
    Route::post('/posts', [AuthorDashboardController::class, 'postsStore'])->name('posts.store');
    Route::get('/posts/{id}/edit', [AuthorDashboardController::class, 'postsEdit'])->name('posts.edit');
    Route::post('/posts/{id}', [AuthorDashboardController::class, 'postsUpdate'])->name('posts.update');
    Route::post('/posts/{id}/submit', [AuthorDashboardController::class, 'postsSubmit'])->name('posts.submit');
    Route::delete('/posts/{id}', [AuthorDashboardController::class, 'postsDestroy'])->name('posts.destroy');
    // Post-publish share screen + manual instant-index ping (own posts only).
    Route::get('/posts/{id}/share', [AuthorDashboardController::class, 'postsShare'])->name('posts.share');
    Route::post('/posts/{id}/instant-index', [AuthorDashboardController::class, 'postsInstantIndex'])->name('posts.instant-index');
    // AI writing assistant (server-side proxy — the API key never reaches the
    // browser). Throttled + daily quota inside the controller.
    Route::post('/ai/generate', [App\Http\Controllers\Frontend\AiAssistantController::class, 'generate'])
        ->name('ai.generate')->middleware('throttle:30,1');
    Route::get('/profile', [AuthorDashboardController::class, 'profileEdit'])->name('profile.edit');
    Route::post('/profile', [AuthorDashboardController::class, 'profileUpdate'])->name('profile.update');
    Route::get('/revenue', [AuthorDashboardController::class, 'revenue'])->name('revenue');
    Route::get('/posting-rules', [AuthorDashboardController::class, 'rules'])->name('rules');
    Route::get('/feedback', [App\Http\Controllers\Frontend\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [App\Http\Controllers\Frontend\FeedbackController::class, 'store'])->name('feedback.store');
    Route::post('/security/2fa/start', [AuthorDashboardController::class, 'start2FA'])->name('2fa.start');
    Route::post('/security/2fa/confirm', [AuthorDashboardController::class, 'confirm2FA'])->name('2fa.confirm');
    Route::post('/security/2fa/disable', [AuthorDashboardController::class, 'disable2FA'])->name('2fa.disable');
    Route::post('/account', [AuthorDashboardController::class, 'accountDelete'])->name('account.delete');
});

// SEO / dynamic text+xml endpoints moved to routes/bots.php (registered
// WITHOUT the web middleware group so crawlers get no session cookie and
// the CDN can cache the responses).

// Admin Auth - slug is /manage for security (no Admin link on frontend)
Route::prefix('manage')->name('admin.')->group(function(){
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Name is 'login.post' so the admin. group prefix yields the name the
    // login view already uses. A doubled prefix here 500'd /manage/login.
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:8,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['admin'])->group(function(){
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Social Auto-Post — publishing automation (admin only). MUST sit
        // before the posts resource for route-matching hygiene.
        Route::get('social-auto-post', [App\Http\Controllers\Admin\SocialController::class, 'index'])->name('social.index');
        Route::post('social-auto-post', [App\Http\Controllers\Admin\SocialController::class, 'update'])->name('social.update');
        Route::post('social-auto-post/test', [App\Http\Controllers\Admin\SocialController::class, 'test'])->name('social.test');
        Route::post('social-auto-post/{publish}/retry', [App\Http\Controllers\Admin\SocialController::class, 'retry'])->name('social.retry');
        // Manual one-click push of any published post to all configured networks.
        Route::post('social-auto-post/push/{post}', [App\Http\Controllers\Admin\SocialController::class, 'pushNow'])->name('social.push');

        // AI Assistant settings (admin configures provider + key + models).
        Route::get('ai-assistant', [App\Http\Controllers\Admin\AiSettingsController::class, 'index'])->name('ai.index');
        Route::post('ai-assistant', [App\Http\Controllers\Admin\AiSettingsController::class, 'update'])->name('ai.update');
        Route::post('ai-assistant/test', [App\Http\Controllers\Admin\AiSettingsController::class, 'test'])->name('ai.test');

        // Multi-author review queue (pending submissions).
        // ⚠️ MUST be registered BEFORE the posts resource: Route::resource()
        // creates GET posts/{post}, which would otherwise capture
        // /manage/posts/review-queue as {post}='review-queue' and crash with
        // "Method PostController::show() does not exist" (HTTP 500).
        Route::get('posts/review-queue', [PostController::class, 'reviewQueue'])->name('posts.review-queue');
        // Server-side autosave for the admin post editor (drafts only).
        // Also registered BEFORE the posts resource (same reason as above).
        Route::post('posts/autosave', [PostController::class, 'autosave'])->name('posts.autosave');
        // Bulk actions on the posts list (tick checkboxes → trash / restore /
        // delete in one click). Registered BEFORE the resource for hygiene.
        Route::post('posts/bulk', [PostController::class, 'bulkAction'])->name('posts.bulk');
        Route::post('posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
        Route::post('posts/{post}/return', [PostController::class, 'return'])->name('posts.return');

        // Posts (no public "show" page inside the admin panel)
        Route::resource('posts', PostController::class)->except(['show']);
        // Post-publish share screen (URL + social share icons) + manual
        // instant-index pings. Registered with the resource group so the
        // admin middleware covers them.
        Route::get('posts/{post}/share', [PostController::class, 'share'])->name('posts.share');
        Route::post('posts/{post}/instant-index', [PostController::class, 'instantIndex'])->name('posts.instant-index');
        Route::post('posts/bulk-instant-index', [PostController::class, 'bulkInstantIndex'])->name('posts.bulk-instant-index');
        Route::post('posts/{post}/toggle', [PostController::class,'toggleStatus'])->name('posts.toggle');
        Route::post('posts/{post}/restore', [PostController::class,'restore'])->name('posts.restore');
        Route::post('posts/{post}/permanent', [PostController::class,'forceDelete'])->name('posts.destroy.permanent');

        // Admin ⇄ User role switch (admin only). See RoleSwitchController.
        Route::post('switch-role', [RoleSwitchController::class, 'switchToUser'])->name('switch-role');

        // Categories
        Route::get('categories', [CategoryController::class,'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class,'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class,'store'])->name('categories.store');
        Route::post('categories/{category}/toggle', [CategoryController::class,'toggle'])->name('categories.toggle');
        Route::get('categories/{category}/edit', [CategoryController::class,'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class,'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class,'destroy'])->name('categories.destroy');
        Route::post('categories/reorder', [CategoryController::class,'reorder'])->name('categories.reorder');

        // Pages (no public "show" page inside the admin panel — the controller
        // has no show() method, so registering it would 500 on /manage/pages/{id})
        Route::resource('pages', AdminPageController::class)->except(['show']);

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
        Route::post('comments/bulk-delete', [AdminCommentController::class,'bulkDestroy'])->name('comments.bulk-delete');
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

        Route::get('feedback', [App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
        Route::get('feedback/{feedback}', [App\Http\Controllers\Admin\FeedbackController::class, 'show'])->name('feedback.show');
        Route::delete('feedback/{feedback}', [App\Http\Controllers\Admin\FeedbackController::class, 'destroy'])->name('feedback.destroy');

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
