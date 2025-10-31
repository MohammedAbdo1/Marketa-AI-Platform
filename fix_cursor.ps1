# Script to fix and restart Cursor quickly
Write-Host "🔧 Fixing Cursor..." -ForegroundColor Cyan

# 1. Kill all Cursor processes
Write-Host "Closing Cursor..." -ForegroundColor Yellow
Get-Process -Name "Cursor" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# 2. Clear cache (optional - uncomment if needed)
# Write-Host "Clearing cache..." -ForegroundColor Yellow
# Remove-Item -Recurse -Force "$env:APPDATA\Cursor\Cache" -ErrorAction SilentlyContinue
# Remove-Item -Recurse -Force "$env:LOCALAPPDATA\Cursor\Cache" -ErrorAction SilentlyContinue

# 3. Restart Cursor
Write-Host "Restarting Cursor..." -ForegroundColor Green
$cursorPath = "$env:LOCALAPPDATA\Programs\cursor\Cursor.exe"
if (Test-Path $cursorPath) {
    Start-Process $cursorPath
    Write-Host "✅ Cursor restarted!" -ForegroundColor Green
} else {
    Write-Host "❌ Cursor not found at: $cursorPath" -ForegroundColor Red
    Write-Host "Please update the path in this script" -ForegroundColor Yellow
}

