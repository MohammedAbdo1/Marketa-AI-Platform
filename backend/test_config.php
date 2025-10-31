<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n========================================\n";
echo "Laravel Configuration Check\n";
echo "========================================\n\n";

echo "APP_ENV: " . config('app.env') . "\n";
echo "PYTHON_AI_URL: " . config('services.python_ai.url') . "\n";
echo "USE_SIMPLE_AI: " . (config('services.python_ai.use_simple_ai') ? 'true' : 'false') . "\n";
echo "SIMPLE_URL: " . config('services.python_ai.simple_url') . "\n";

echo "\n========================================\n";
echo "Testing Connection to AI Service\n";
echo "========================================\n\n";

$url = config('services.python_ai.url');
echo "Trying to connect to: $url\n";

try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "[FAILED] cURL Error: $error\n";
    } else {
        echo "[SUCCESS] HTTP $httpCode\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}

echo "\n========================================\n";

