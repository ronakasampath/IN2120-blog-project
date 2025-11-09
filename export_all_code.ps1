# Export all code files into a single text file
# Save as: export_all_code.ps1 (run from project root)

$outFile = Join-Path $PSScriptRoot 'all_codes_dump.txt'
if (Test-Path $outFile) { Remove-Item $outFile -Force }

# File types to include
$includes = @('*.php','*.js','*.css','*.env','*.sql','*.txt')

Get-ChildItem -Path $PSScriptRoot -Recurse -File | Where-Object { $includes -contains $_.Extension -or ($includes -contains ('*' + $_.Extension)) } | Sort-Object FullName | ForEach-Object {
    $header = "===== " + ($_.FullName) + " ====="
    Add-Content -Path $outFile -Value $header -Encoding UTF8
    try {
        $content = Get-Content -Path $_.FullName -Raw -ErrorAction Stop
        Add-Content -Path $outFile -Value $content -Encoding UTF8
    } catch {
        Add-Content -Path $outFile -Value "[ERROR reading file: $($_.FullName) - $($_.Exception.Message)]" -Encoding UTF8
    }
    Add-Content -Path $outFile -Value "`n" -Encoding UTF8
}

Write-Host "Created file: $outFile" -ForegroundColor Green
