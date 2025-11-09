<!-- 49595dcc-5a37-414f-8723-a505b25ec39c e0df0272-b792-4507-af8f-4b8815e2956f -->
# Creative Assets Unification Plan

## Overview

- Establish a single `creative_assets` table to hold campaign posts, designs, and future asset types.
- Migrate existing `campaign_posts` data and update Laravel + Vue layers to read from the new source while keeping service continuity.

## Steps

1. **Schema-setup**

- Design new migration for `creative_assets` (or refactor `designs`) with shared columns (`asset_type`, `settings`, `metadata`, context, file references, dimensions, status, ownership).
- Ensure indexes exist for `user_id`, `organization_id`, `asset_type`, `context_type/context_id`.

2. **Model + scaffolding**

- Create Laravel model `CreativeAsset` (or extend `Design`) with type scopes (`campaignPosts()`, `brandAssets()`), casts for JSON fields, and relationships to `Campaign`, `User`, etc.
- Add repository/service helpers to fetch/save creative assets instead of raw table queries.

3. **Campaign posts migration path**

- Write migration/command to iterate `campaign_posts`, insert matching `creative_assets` records (`asset_type='campaign_post'`) while storing post-specific data in `settings` JSON.
- Add temporary FK `campaign_posts.creative_asset_id` or a legacy ID column on `creative_assets` for rollback.

4. **API + frontend switch-over**

- Update Laravel controllers/services (e.g. `CampaignController`, AI generation services) to use `CreativeAsset` scopes instead of `campaign_posts` queries.
- Adjust Pinia stores and Vue components (`CampaignWizard.vue`, `CampaignDetails.vue`, etc.) to consume the unified payload while preserving current UX.

5. **Clean-up & brand assimilation**

- After verifying campaigns work, migrate brand/design tables into `creative_assets` (new `asset_type` values).
- Remove or convert old tables to views, update documentation, and add tests/seeders for the unified model.

## Notes

- Keep migrations idempotent and add feature flags if needed for phased rollout.
- Plan for data backup before large migrations.
- Document `settings` JSON schema per `asset_type` for future contributors.

### To-dos

- [ ] إعادة تصميم Database Schema: إزالة حقول اللغات الثابتة، إضافة نظام مرن، Draft Management fields، Campaign Intelligence fields
- [ ] تطوير Smart Prompt Engineering في Planner Agent: فهم اللغات تلقائياً، توليد Campaign Strategy، Daily Calendar، Content Briefs
- [ ] تحسين Image Generation مع Composition System: تحليل تفضيلات التصميم، توليد text layers ذكية، دعم متعدد اللغات
- [ ] بناء Draft Management APIs: create draft, save progress, get drafts, delete draft endpoints
- [ ] تحسين Preview API: إرجاع Campaign Strategy كاملة، Timeline مفصل، Sample Posts مع Briefs، Estimated Metrics
- [ ] إعادة تصميم Campaign Wizard: Draft detection، Auto-save system، حذف اختيار اللغة، تحسين UX Flow
- [ ] بناء Draft Resume Dialog Component: عرض drafts، resume/discard options، progress indicators
- [ ] إعادة تصميم Step 4 Preview: Executive Summary، Strategic Phases، Timeline Visualization، Sample Posts Carousel، Metrics Dashboard
- [ ] بناء Timeline Visualization Component: عرض يومي وأسبوعي، interactive، expandable days
- [ ] بناء Sample Post Card Component: عرض صورة مع text overlays، Content Brief expandable، Expected Results
- [ ] تطبيق Auto-Save System: حفظ كل 30 ثانية، حفظ عند تغيير الخطوة، حفظ عند الخروج
- [ ] تحسين Campaign Details Page: عرض Strategy، Timeline Calendar، Posts مع Briefs، Editor integration
- [ ] دمج Post Editor مع Composition System: تعديل text layers، تغيير styles، حفظ changes
- [ ] تنفيذ سيناريوهات الاختبار: Draft management، Language intelligence، Campaign quality، Editor functionality
- [ ] تحسين الأداء: Lazy loading، Image optimization، API caching، State management optimization