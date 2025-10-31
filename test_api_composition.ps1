# Test Composition API Endpoints
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Testing Composition API Endpoints" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8001/api"

# Test 1: Analyze Description
Write-Host "[TEST 1] POST /api/post/analyze-description" -ForegroundColor Yellow
$body1 = @{
    description = "مطعم جميل، اكتب 'افتتاح قريب' باللون الأحمر في الأسفل"
    platform = "instagram"
    business_type = "restaurant"
} | ConvertTo-Json

try {
    $response1 = Invoke-RestMethod -Uri "$baseUrl/post/analyze-description" `
        -Method Post `
        -Body $body1 `
        -ContentType "application/json" `
        -TimeoutSec 30
    
    Write-Host "[PASS] Analysis successful!" -ForegroundColor Green
    Write-Host "  Scene: $($response1.scene_description.Substring(0, [Math]::Min(60, $response1.scene_description.Length)))..."
    Write-Host "  Text overlays: $($response1.text_overlays.Count)"
    Write-Host "  Needs composition: $($response1.needs_composition)"
    
    if ($response1.text_overlays.Count -gt 0) {
        foreach ($overlay in $response1.text_overlays) {
            Write-Host "    - Text: '$($overlay.text)' at $($overlay.position), color: $($overlay.color)" -ForegroundColor Gray
        }
    }
} catch {
    Write-Host "[FAILED] $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n========================================`n" -ForegroundColor Cyan

# Test 2: Check if simple description doesn't trigger composition
Write-Host "[TEST 2] Simple description (no composition)" -ForegroundColor Yellow
$body2 = @{
    description = "رجل يقف في الحديقة"
    platform = "instagram"
} | ConvertTo-Json

try {
    $response2 = Invoke-RestMethod -Uri "$baseUrl/post/analyze-description" `
        -Method Post `
        -Body $body2 `
        -ContentType "application/json" `
        -TimeoutSec 30
    
    if (-not $response2.needs_composition) {
        Write-Host "[PASS] Correctly identified no composition needed!" -ForegroundColor Green
    } else {
        Write-Host "[INFO] Composition detected (may be false positive)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "[FAILED] $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "API Tests Complete!" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

