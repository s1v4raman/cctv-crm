param (
    [int]$MaxIterations = 10
)

# 1. Verify todo.md exists
if (-not (Test-Path ".\todo.md")) {
    Write-Host " Error: 'todo.md' not found in $(Get-Location)." -ForegroundColor Red
    Write-Host "Please create a todo.md file with '- [ ] Task description' items first." -ForegroundColor Yellow
    exit 1
}

$iteration = 0

while ($iteration -lt $MaxIterations) {
    # 2. Check if any unchecked items remain
    $remaining = Select-String -Path ".\todo.md" -Pattern "- \[ \]"
    if (-not $remaining) {
        Write-Host " All tasks in todo.md are completed!" -ForegroundColor Green
        break
    }

    $iteration++
    Write-Host " Starting Ralph Loop Iteration #$iteration..." -ForegroundColor Cyan
    Write-Host "Pending tasks remaining: $($remaining.Count)" -ForegroundColor Yellow
    Write-Host "Next task: $($remaining[0].Line.Trim())" -ForegroundColor White

    # Placeholder for agent execution step
    Write-Host "Ready for execution." -ForegroundColor Green
    break
}
