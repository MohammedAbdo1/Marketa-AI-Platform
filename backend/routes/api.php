<?php

use App\Http\Controllers\Api\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Api\Admin\Auth\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\Admin\CMSController;
use App\Http\Controllers\Api\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Api\Admin\OrganizationController;
use App\Http\Controllers\Api\Admin\PlanController;
use App\Http\Controllers\Api\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CampaignPostController;
use App\Http\Controllers\Api\DesignController;
use App\Http\Controllers\Api\AiConversationController;
use App\Http\Controllers\Api\ImageProxyController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\Admin\AIDiagnosticsController as AdminAIDiagnosticsController;
use App\Http\Controllers\Api\AIDiagnosticsController;
use App\Http\Controllers\Api\UsageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════════
// 🌍 Public Routes (No Auth Required)
// ═══════════════════════════════════════════════════════════════════

// Image proxy (no auth required)
Route::get('images/{filename}', [ImageProxyController::class, 'show'])
     ->name('images.proxy');

// CMS - Public pages
Route::get('pages/{slug}', [PublicController::class, 'getPage']);
Route::get('testimonials', [PublicController::class, 'getTestimonials']);
Route::get('faqs', [PublicController::class, 'getFaqs']);
Route::get('plans', [PublicController::class, 'getPlans']);

// ═══════════════════════════════════════════════════════════════════
// 🔵 User Routes (Web & Mobile Apps)
// ═══════════════════════════════════════════════════════════════════

// Authentication
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('register', [RegisterController::class, 'register'])->name('register');

// Google OAuth
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Email Verification
Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('email/resend', [EmailVerificationController::class, 'resend'])
    ->name('verification.resend');

Route::post('resend-verification', [EmailVerificationController::class, 'resend'])
    ->name('verification.resend.alt');

// Protected User Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth & Profile
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Email Verification (for logged-in users)
    Route::post('email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // Usage & Statistics
    Route::prefix('usage')->group(function () {
        Route::get('daily', [UsageController::class, 'daily']);
        Route::get('history', [UsageController::class, 'history']);
        Route::get('summary', [UsageController::class, 'summary']);
    });

    // Brands Management
    Route::apiResource('brands', BrandController::class);
    Route::post('brands/{id}/logo', [BrandController::class, 'uploadLogo']);

    // Campaigns Management
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/preview', [CampaignController::class, 'generatePreview']);
    Route::post('campaigns/{campaign}/generate', [CampaignController::class, 'generate']);
    Route::get('campaigns/{campaign}/status', [CampaignController::class, 'generationStatus']);
    Route::post('campaigns/suggest-colors', [CampaignController::class, 'suggestColors']);
    Route::post('campaigns/{campaign}/select-plan', [CampaignController::class, 'selectPlan']);
    Route::get('campaigns/{campaign}/posts', [CampaignController::class, 'posts']);
    Route::get('campaigns/{campaign}/calendar', [CampaignController::class, 'calendar']);

    // Campaign Posts Management
    Route::get('campaign-posts/{id}', [CampaignPostController::class, 'show']);
    Route::put('campaign-posts/{id}', [CampaignPostController::class, 'update']);
    Route::delete('campaign-posts/{id}', [CampaignPostController::class, 'destroy']);
    Route::post('campaign-posts/{id}/approve', [CampaignPostController::class, 'approve']);
    Route::post('campaign-posts/{id}/reject', [CampaignPostController::class, 'reject']);
    Route::post('campaign-posts/{id}/regenerate', [CampaignPostController::class, 'regenerate']);
    Route::post('campaign-posts/{id}/generate-media', [CampaignPostController::class, 'generateMedia']);
    Route::put('campaign-posts/{id}/schedule', [CampaignPostController::class, 'schedule']);

    // Layer Management & Composition (for image editing)
    Route::get('campaign-posts/{id}/layers', [CampaignPostController::class, 'exportLayers']);
    Route::post('campaign-posts/{id}/layers', [CampaignPostController::class, 'addLayer']);
    Route::put('campaign-posts/{id}/layers/{layerIndex}', [CampaignPostController::class, 'updateLayer']);
    Route::delete('campaign-posts/{id}/layers/{layerIndex}', [CampaignPostController::class, 'removeLayer']);
    Route::post('campaign-posts/{id}/layers/import', [CampaignPostController::class, 'importLayers']);
    Route::post('campaign-posts/{id}/layers/{layerIndex}/regenerate', [CampaignPostController::class, 'regenerateLayer']);

    // Designs Management (New Unified System)
    Route::get('designs/templates', [DesignController::class, 'templates'])->name('designs.templates');
    Route::apiResource('designs', DesignController::class)->parameters(['designs' => 'design']);
    Route::post('designs/{design}/duplicate', [DesignController::class, 'duplicate'])->name('designs.duplicate');
    Route::post('designs/{design}/export', [DesignController::class, 'export'])->name('designs.export');
    Route::post('designs/{design}/template', [DesignController::class, 'toTemplate'])->name('designs.template');

    // AI Conversations & Studio
    Route::prefix('ai')->group(function () {
        Route::apiResource('conversations', AiConversationController::class)
             ->parameters(['conversations' => 'conversation']);
        Route::post('conversations/{conversation}/messages', [AiConversationController::class, 'sendMessage'])
             ->name('ai.conversations.messages');
        
        // AI Diagnostics (User-facing)
        Route::post('test-text', [AIDiagnosticsController::class, 'testText'])->name('ai.test-text');
        Route::post('test-image', [AIDiagnosticsController::class, 'testImage'])->name('ai.test-image');
    });

    // Campaign-Design Linking
    Route::post('campaigns/{campaign}/designs', [CampaignController::class, 'attachDesign'])->name('campaigns.designs.attach');
    Route::delete('campaigns/{campaign}/designs/{design}', [CampaignController::class, 'detachDesign'])->name('campaigns.designs.detach');
});

// ═══════════════════════════════════════════════════════════════════
// 🔴 Admin Routes (with /admin prefix)
// ═══════════════════════════════════════════════════════════════════

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Auth (Public)
    Route::post('login', [AdminLoginController::class, 'login'])->name('login');

    // Protected Admin Routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        // Auth
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('me', [AdminLoginController::class, 'me'])->name('me');
        Route::get('profile', [AdminProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // ═══════════════════════════════════════════════════════════════════
        // Platform Management (Admins, Roles, Permissions)
        // ═══════════════════════════════════════════════════════════════════
        
        // Admins Management (Platform Admins only)
        Route::get('admins', [UserController::class, 'admins'])->name('admins.index');
        
        // Roles Management
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
        
        // Permissions Management
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

        // ═══════════════════════════════════════════════════════════════════
        // Customer Management (Platform Customers)
        // ═══════════════════════════════════════════════════════════════════
        
        // Customers (Regular Users - not Admins)
        Route::get('customers', [UserController::class, 'customers'])->name('customers.index');
        Route::get('customers/{uuid}', [UserController::class, 'show'])->name('customers.show');
        Route::get('customers/{uuid}/details', [UserController::class, 'details'])->name('customers.details');
        Route::put('customers/{uuid}', [UserController::class, 'update'])->name('customers.update');
        Route::delete('customers/{uuid}', [UserController::class, 'destroy'])->name('customers.destroy');
        Route::patch('customers/{uuid}/status', [UserController::class, 'updateStatus'])->name('customers.status');

        // ═══════════════════════════════════════════════════════════════════
        // General Management
        // ═══════════════════════════════════════════════════════════════════

        // Users Management (All users - for backward compatibility)
        Route::apiResource('users', UserController::class);
        Route::patch('users/{uuid}/status', [UserController::class, 'updateStatus'])->name('users.status');

        // Plans Management
        Route::apiResource('plans', PlanController::class);

        // Organizations Management
        Route::apiResource('organizations', OrganizationController::class);

        // ═══════════════════════════════════════════════════════════════════
        // CMS Management
        // ═══════════════════════════════════════════════════════════════════
        
        // Pages
        Route::get('cms/pages', [CMSController::class, 'getPages']);
        Route::put('cms/pages/{id}', [CMSController::class, 'updatePage']);
        
        // Sections
        Route::get('cms/sections', [CMSController::class, 'getSections']);
        Route::post('cms/sections', [CMSController::class, 'createSection']);
        Route::put('cms/sections/{id}', [CMSController::class, 'updateSection']);
        Route::delete('cms/sections/{id}', [CMSController::class, 'deleteSection']);
        
        // Content
        Route::post('cms/content', [CMSController::class, 'createContent']);
        Route::put('cms/content/{id}', [CMSController::class, 'updateContent']);
        Route::delete('cms/content/{id}', [CMSController::class, 'deleteContent']);
        
        // Testimonials & FAQs
        Route::apiResource('testimonials', AdminTestimonialController::class);
        Route::apiResource('faqs', AdminFaqController::class);

        // AI Diagnostics (Admin only)
        Route::post('ai/test-text', [AdminAIDiagnosticsController::class, 'testText'])->name('ai.test-text');
        Route::post('ai/test-image', [AdminAIDiagnosticsController::class, 'testImage'])->name('ai.test-image');
    });
});

