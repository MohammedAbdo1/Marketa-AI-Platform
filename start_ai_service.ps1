# Start AI Service for Development
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Starting Marketa AI Service" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Set-Location -Path "$PSScriptRoot\ai-service"

Write-Host "[INFO] Starting AI Service on http://localhost:8001" -ForegroundColor Yellow
Write-Host "[INFO] Press Ctrl+C to stop`n" -ForegroundColor Yellow

python run.py

