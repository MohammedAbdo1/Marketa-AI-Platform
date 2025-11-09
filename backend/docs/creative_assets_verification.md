# Creative Assets Migration – Verification Checklist

1. **Database migrations**
   - `php artisan migrate` (from `backend/`) – ensure the `creative_assets` table exists and legacy `campaign_posts` tables are dropped.
2. **Data validation**
   - Spot-check recent campaigns and confirm their posts appear under `creative_assets` with correct metadata (title, platform, schedule).
   - Verify `ai_requests` rows now reference `creative_asset_id`.
3. **Application smoke tests**
   - Load campaign details page; confirm posts render as before.
   - Open a post in the editor, save changes, and verify the campaign view updates immediately.


