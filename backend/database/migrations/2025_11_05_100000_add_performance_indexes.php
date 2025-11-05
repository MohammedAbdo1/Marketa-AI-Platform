<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds critical indexes for performance optimization.
     * These indexes target the most frequently queried relationships.
     */
    public function up(): void
    {
        // Subscriptions - Critical for activeSubscription() relation
        Schema::table('subscriptions', function (Blueprint $table) {
            // Composite index for finding active subscriptions by user
            $table->index(['user_id', 'status'], 'idx_subscriptions_user_status');
            
            // Composite index including soft deletes for optimal active subscription lookup
            $table->index(['user_id', 'status', 'deleted_at'], 'idx_subscriptions_active_lookup');
            
            // Index for organization-wide subscription queries
            $table->index(['organization_id', 'status'], 'idx_subscriptions_org_status');
        });

        // Users - Frequently joined table
        Schema::table('users', function (Blueprint $table) {
            // Index for organization relationship
            $table->index('organization_id', 'idx_users_organization');
            
            // Composite index for active user queries with soft deletes
            $table->index(['status', 'deleted_at'], 'idx_users_active');
            
            // Index for email verification status queries
            $table->index('email_verified_at', 'idx_users_email_verified');
        });

        // Daily Usage - Queried on every dashboard load
        Schema::table('daily_usage', function (Blueprint $table) {
            // Composite index for user's daily usage lookup
            $table->index(['user_id', 'date'], 'idx_daily_usage_user_date');
            
            // Index for date-based analytics queries
            $table->index('date', 'idx_daily_usage_date');
        });

        // Designs - Heavily queried in dashboard
        Schema::table('designs', function (Blueprint $table) {
            // Composite index for user's active designs
            $table->index(['user_id', 'trashed_at'], 'idx_designs_user_active');
            
            // Index for finding designs by type
            $table->index('type', 'idx_designs_type');
            
            // Composite index for user's designs by type
            $table->index(['user_id', 'type', 'trashed_at'], 'idx_designs_user_type_active');
        });

        // Brands - Frequently accessed
        Schema::table('brands', function (Blueprint $table) {
            // Index for user's brands
            $table->index('user_id', 'idx_brands_user');
            
            // Composite index for active brands
            $table->index(['user_id', 'deleted_at'], 'idx_brands_user_active');
        });

        // Campaigns - Core feature
        Schema::table('campaigns', function (Blueprint $table) {
            // Composite index for user's campaigns by status
            $table->index(['user_id', 'status'], 'idx_campaigns_user_status');
            
            // Index for brand relationship
            $table->index('brand_id', 'idx_campaigns_brand');
            
            // Composite index for active campaigns
            $table->index(['user_id', 'status', 'deleted_at'], 'idx_campaigns_user_active');
        });

        // User Favorites - For quick favorite lookups
        Schema::table('user_favorites', function (Blueprint $table) {
            // Composite index for user's favorites by section
            $table->index(['user_id', 'favorite_section_id'], 'idx_favorites_user_section');
            
            // Index for design relationship
            $table->index('design_id', 'idx_favorites_design');
        });

        // AI Conversations - For AI Studio
        Schema::table('ai_conversations', function (Blueprint $table) {
            // Composite index for user's conversations
            $table->index(['user_id', 'deleted_at'], 'idx_conversations_user_active');
            
            // Index for recent conversations
            $table->index('updated_at', 'idx_conversations_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_user_status');
            $table->dropIndex('idx_subscriptions_active_lookup');
            $table->dropIndex('idx_subscriptions_org_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_organization');
            $table->dropIndex('idx_users_active');
            $table->dropIndex('idx_users_email_verified');
        });

        Schema::table('daily_usage', function (Blueprint $table) {
            $table->dropIndex('idx_daily_usage_user_date');
            $table->dropIndex('idx_daily_usage_date');
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->dropIndex('idx_designs_user_active');
            $table->dropIndex('idx_designs_type');
            $table->dropIndex('idx_designs_user_type_active');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex('idx_brands_user');
            $table->dropIndex('idx_brands_user_active');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex('idx_campaigns_user_status');
            $table->dropIndex('idx_campaigns_brand');
            $table->dropIndex('idx_campaigns_user_active');
        });

        Schema::table('user_favorites', function (Blueprint $table) {
            $table->dropIndex('idx_favorites_user_section');
            $table->dropIndex('idx_favorites_design');
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropIndex('idx_conversations_user_active');
            $table->dropIndex('idx_conversations_updated');
        });
    }
};

