<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\GenerateCampaignPosts;
use App\Models\Campaign;
use App\Services\PythonAIService;
use Illuminate\Support\Facades\Log;

$campaignId = 44;
$campaign = Campaign::find($campaignId);

if (!$campaign) {
    echo "Campaign with ID {$campaignId} not found." . PHP_EOL;
    exit(1);
}

echo "Testing job for campaign: " . $campaign->name . PHP_EOL;

try {
    $job = new GenerateCampaignPosts($campaign);
    $aiService = app(PythonAIService::class);
    echo "Starting job execution..." . PHP_EOL;
    $job->handle($aiService);
    echo "Job completed successfully." . PHP_EOL;
} catch (Exception $e) {
    echo "Job failed: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

