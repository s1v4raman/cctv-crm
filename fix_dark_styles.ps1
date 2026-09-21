$files = @(
    'resources/views/suppliers/index.blade.php',
    'resources/views/quotations/index.blade.php',
    'resources/views/purchase_orders/index.blade.php',
    'resources/views/products/index.blade.php',
    'resources/views/equipment/index.blade.php',
    'resources/views/admin/users/index.blade.php',
    'resources/views/leads/index.blade.php',
    'resources/views/jobs/index.blade.php'
)

foreach ($file in $files) {
    $fullPath = Join-Path (Get-Location) $file
    if (Test-Path $fullPath) {
        $content = [System.IO.File]::ReadAllText($fullPath)
        
        # Remove hardcoded dark .pg-wrap background rule
        $content = $content -replace '\.pg-wrap\s*\{\s*background\s*:\s*#060913[^}]*\}', '/* .pg-wrap uses global app.css */'
        
        # Remove hardcoded .pg-inner (layout handled by global)
        $content = $content -replace '\.pg-inner\s*\{\s*max-width[^}]*\}', '/* .pg-inner uses global app.css */'
        
        # Fix filter-bar input backgrounds in dark-only
        $content = $content -replace 'background:\s*#060913;\s*outline:none', 'outline:none'
        
        [System.IO.File]::WriteAllText($fullPath, $content)
        Write-Host "Fixed: $file"
    } else {
        Write-Host "Not found: $file"
    }
}
Write-Host "Done!"
