<?php

namespace App\Services;

use App\Models\Campaign;
use Carbon\Carbon;

class CampaignService extends BaseService
{
    /**
     * Get all campaigns for an organization
     */
    public function getCampaignsForOrganization(int $organizationId)
    {
        return Campaign::where('organization_id', $organizationId)
            ->with(['brand', 'postAssets'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new campaign
     */
    public function createCampaign(array $data, int $organizationId)
    {
        $data['organization_id'] = $organizationId;
        
        // Calculate end_date if not provided
        if (!isset($data['end_date']) && isset($data['start_date'], $data['duration_days'])) {
            $data['end_date'] = Carbon::parse($data['start_date'])
                ->addDays($data['duration_days'])
                ->toDateString();
        }
        
        return Campaign::create($data);
    }

    /**
     * Update a campaign
     */
    public function updateCampaign(int $id, array $data, int $organizationId)
    {
        $campaign = Campaign::where('id', $id)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $campaign->update($data);
        
        return $campaign;
    }

    /**
     * Delete a campaign
     */
    public function deleteCampaign(int $id, int $organizationId)
    {
        $campaign = Campaign::where('id', $id)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        return $campaign->delete();
    }

    /**
     * Get a single campaign with all details
     */
    public function getCampaign(int $id, int $organizationId)
    {
        return Campaign::where('id', $id)
            ->where('organization_id', $organizationId)
            ->with(['brand', 'postAssets'])
            ->firstOrFail();
    }

    /**
     * Generate AI campaign plans
     * This is a placeholder - will be implemented in AIContentGeneratorService
     */
    public function generateAIPlans(int $campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);
        
        // Update campaign status to generating
        $campaign->update(['status' => 'generating']);
        
        // TODO: Call AIContentGeneratorService to generate plans
        // For now, return placeholder
        
        return [
            'message' => 'AI generation started',
            'campaign_id' => $campaignId,
        ];
    }

    /**
     * Select a plan from generated plans
     */
    public function selectPlan(int $campaignId, int $planIndex, int $organizationId)
    {
        $campaign = Campaign::where('id', $campaignId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $campaign->update([
            'selected_plan_index' => $planIndex,
            'status' => 'ready',
        ]);
        
        return $campaign;
    }

    /**
     * Get campaign posts
     */
    public function getCampaignPosts(int $campaignId, int $organizationId)
    {
        $campaign = Campaign::where('id', $campaignId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        return $campaign->postAssets()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get campaign calendar view
     */
    public function getCampaignCalendar(int $campaignId, int $organizationId, $startDate, $endDate)
    {
        $campaign = Campaign::where('id', $campaignId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $posts = $campaign->postAssets()
            ->get()
            ->filter(function ($asset) use ($startDate, $endDate) {
                $scheduled = data_get($asset->settings, 'scheduled_date');
                if (!$scheduled) {
                    return false;
                }

                return $scheduled >= $startDate && $scheduled <= $endDate;
            })
            ->sortBy(function ($asset) {
                return [
                    data_get($asset->settings, 'scheduled_date'),
                    data_get($asset->settings, 'scheduled_time'),
                ];
            });

        return $posts->groupBy(fn ($asset) => data_get($asset->settings, 'scheduled_date'));
    }
}

