<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDataSyncController;
use App\Http\Controllers\Admin\AdminPortfolioController;
use App\Http\Controllers\AgentDiscoveryController;
use App\Http\Controllers\CredentialArchiveController;
use App\Http\Controllers\DesignMediaArchiveController;
use App\Http\Controllers\GuestbookController;
use App\Http\Controllers\PortfolioThumbnailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ViewerPresenceController;
use App\Http\Middleware\AddNoIndexHeader;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', AgentDiscoveryController::class)->name('agent-discovery');
Route::get('/media/thumbnail', PortfolioThumbnailController::class)->name('portfolio-thumbnail');

Route::get('/', ProfileController::class)->name('profile.show');
Route::get('/services', ServicesController::class)->name('services.index');
Route::get('/badges', [CredentialArchiveController::class, 'badges'])->name('badges.index');
Route::get('/certifications', [CredentialArchiveController::class, 'certificates'])->name('certifications.index');
Route::get('/designs', DesignMediaArchiveController::class)->name('designs.index');
Route::get('/viewer-presence', ViewerPresenceController::class)
    ->middleware('throttle:20,1')
    ->name('viewer-presence');
Route::post('/guestbook', [GuestbookController::class, 'store'])
    ->middleware('throttle:4,1')
    ->name('guestbook.store');

Route::prefix('admin')->name('admin.')->middleware(AddNoIndexHeader::class)->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');

    Route::middleware('portfolio.admin')->group(function (): void {
        Route::get('/', [AdminPortfolioController::class, 'index'])->name('index');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::put('/profile', [AdminPortfolioController::class, 'updateProfile'])->name('profile.update');

        Route::post('/sync/certificates', [AdminDataSyncController::class, 'certificates'])->middleware('throttle:6,1')->name('sync.certificates');
        Route::post('/sync/badges', [AdminDataSyncController::class, 'badges'])->middleware('throttle:6,1')->name('sync.badges');
        Route::post('/sync/github', [AdminDataSyncController::class, 'github'])->middleware('throttle:6,1')->name('sync.github');

        Route::post('/achievements', [AdminPortfolioController::class, 'storeAchievement'])->name('achievements.store');
        Route::put('/achievements/{achievement}', [AdminPortfolioController::class, 'updateAchievement'])->name('achievements.update');
        Route::delete('/achievements/{achievement}', [AdminPortfolioController::class, 'destroyAchievement'])->name('achievements.destroy');

        Route::post('/projects', [AdminPortfolioController::class, 'storeProject'])->name('projects.store');
        Route::put('/projects/{project}', [AdminPortfolioController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [AdminPortfolioController::class, 'destroyProject'])->name('projects.destroy');

        Route::post('/design-media', [AdminPortfolioController::class, 'storeDesignMedia'])->name('design-media.store');
        Route::put('/design-media/{designMedia}', [AdminPortfolioController::class, 'updateDesignMedia'])->name('design-media.update');
        Route::delete('/design-media/{designMedia}', [AdminPortfolioController::class, 'destroyDesignMedia'])->name('design-media.destroy');

        Route::post('/portfolio-tools', [AdminPortfolioController::class, 'storePortfolioTool'])->name('portfolio-tools.store');
        Route::put('/portfolio-tools/{portfolioTool}', [AdminPortfolioController::class, 'updatePortfolioTool'])->name('portfolio-tools.update');
        Route::delete('/portfolio-tools/{portfolioTool}', [AdminPortfolioController::class, 'destroyPortfolioTool'])->name('portfolio-tools.destroy');

        Route::post('/published-works', [AdminPortfolioController::class, 'storePublishedWork'])->name('published-works.store');
        Route::put('/published-works/{publishedWork}', [AdminPortfolioController::class, 'updatePublishedWork'])->name('published-works.update');
        Route::delete('/published-works/{publishedWork}', [AdminPortfolioController::class, 'destroyPublishedWork'])->name('published-works.destroy');

        Route::post('/work-experiences', [AdminPortfolioController::class, 'storeWorkExperience'])->name('work-experiences.store');
        Route::put('/work-experiences/{workExperience}', [AdminPortfolioController::class, 'updateWorkExperience'])->name('work-experiences.update');
        Route::delete('/work-experiences/{workExperience}', [AdminPortfolioController::class, 'destroyWorkExperience'])->name('work-experiences.destroy');

        Route::post('/education', [AdminPortfolioController::class, 'storeEducation'])->name('education.store');
        Route::put('/education/{education}', [AdminPortfolioController::class, 'updateEducation'])->name('education.update');
        Route::delete('/education/{education}', [AdminPortfolioController::class, 'destroyEducation'])->name('education.destroy');

        Route::post('/credential-badges', [AdminPortfolioController::class, 'storeCredentialBadge'])->name('credential-badges.store');
        Route::put('/credential-badges/{credentialBadge}', [AdminPortfolioController::class, 'updateCredentialBadge'])->name('credential-badges.update');
        Route::delete('/credential-badges/{credentialBadge}', [AdminPortfolioController::class, 'destroyCredentialBadge'])->name('credential-badges.destroy');

        Route::post('/certificates', [AdminPortfolioController::class, 'storeCertificate'])->name('certificates.store');
        Route::put('/certificates/{certificate}', [AdminPortfolioController::class, 'updateCertificate'])->name('certificates.update');
        Route::delete('/certificates/{certificate}', [AdminPortfolioController::class, 'destroyCertificate'])->name('certificates.destroy');

        Route::put('/guestbook/{guestbookEntry}', [AdminPortfolioController::class, 'updateGuestbookEntry'])->name('guestbook.update');
        Route::post('/guestbook/{guestbookEntry}/notification', [AdminPortfolioController::class, 'retryGuestbookNotification'])->middleware('throttle:6,1')->name('guestbook.notification.retry');
        Route::delete('/guestbook/{guestbookEntry}', [AdminPortfolioController::class, 'destroyGuestbookEntry'])->name('guestbook.destroy');
    });
});
