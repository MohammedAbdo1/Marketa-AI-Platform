<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $campaign = App\Models\Campaign::find(40);
    if (!$campaign) {
        echo "Campaign not found\n";
        exit(1);
    }
    
    echo "Testing job for campaign: " . $campaign->name . "\n";
    
    $job = new App\Jobs\GenerateCampaignPosts($campaign);
    $aiService = app(App\Services\PythonAIService::class);
    
    echo "Starting job execution...\n";
    $job->handle($aiService);
    echo "Job completed successfully\n";
    
} catch (Exception $e) {
    echo "Job failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

