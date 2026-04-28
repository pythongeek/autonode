$dir = "f:\My profession\plugins\n8n rank math field access helper\autonode\autonode"
$files = Get-ChildItem -Path $dir -Recurse -Include *.php,*.js,*.css,*.txt

foreach ($file in $files) {
    if ($file.Name -eq "rename.ps1" -or $file.Name -eq "readme.txt") {
        # We will handle readme.txt separately if needed, or include it.
        # Let's include readme.txt
    }
    if ($file.Name -eq "rename.ps1") { continue }

    $content = Get-Content -Path $file.FullName -Raw
    $original = $content
    
    # 1. Namespaces and prefixes
    $content = $content -creplace 'AMP_CM_', 'AUTONODE_'
    $content = $content -creplace 'AMP_CM', 'AutoNode'
    $content = $content -creplace 'amp_cm_', 'autonode_'
    
    # 2. Brand names
    $content = $content -replace 'Agentic Marketing Pro', 'BdowneerTech'
    $content = $content -replace 'agenticmarketingpro\.com', 'autonode.wikiofautomation.com'
    $content = $content -replace 'agenticmarketingpro', 'bdowneertech'
    
    # 3. REST API route and slugs
    $content = $content -replace 'amp-agency', 'autonode'
    $content = $content -replace 'amp-cm-', 'autonode-'
    
    # 4. Email
    $content = $content -replace 'jemieemoree@gmail\.com', 'inquiry@autonode.wikiofautomation.com'

    if ($original -ne $content) {
        Write-Host "Updating: $($file.FullName)"
        [IO.File]::WriteAllText($file.FullName, $content)
    }
}
Write-Host "Done"
