<?php

use App\Http\Controllers\Frontend\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Crawler endpoints (robots.txt, sitemap.xml, llms.txt, ads.txt)
|--------------------------------------------------------------------------
| These live OUTSIDE the "web" middleware group on purpose: no session,
| no cookie, no CSRF — just a fast plain response with a short public
| Cache-Control header so Hostinger's edge cache (hcdn) can serve them.
| Ahrefs' audit "Slow server response for AI crawlers" was caused by AI
| bots paying a full session/bootstrap for robots.txt and sitemap.xml on
| every fetch. Maintenance mode still applies to this file because the
| EnsureSiteIsLive middleware is registered globally.
*/

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/ads.txt', [SeoController::class, 'ads'])->name('seo.ads');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('seo.llms');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
