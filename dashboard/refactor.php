<?php
$files = glob('*.php');
$htmlFiles = glob('*.html');
$allFiles = array_merge($files, $htmlFiles);

$baseStyleContent = '';

// First get the style from header.php
$headerContent = file_get_contents('header.php');
if (preg_match('/<style>(.*?)<\/style>/s', $headerContent, $matches)) {
    $baseStyleContent = $matches[1];
    file_put_contents('style.css', $baseStyleContent);
    $headerContent = preg_replace('/<style>.*?<\/style>/s', '<link rel="stylesheet" href="style.css">', $headerContent);
    file_put_contents('header.php', $headerContent);
    echo "Extracted style to style.css and updated header.php\n";
}

$baseStyleLen = strlen($baseStyleContent);

foreach ($allFiles as $file) {
    if ($file === 'header.php') continue;
    
    $content = file_get_contents($file);
    $updated = false;
    
    // remove matching style blocks (if they are very large > 10000 chars, likely the same dashboard style)
    $content = preg_replace_callback('/<style>(.*?)<\/style>/s', function($m) {
        if (strlen($m[1]) > 10000) {
            return '<link rel="stylesheet" href="style.css">';
        }
        return $m[0]; // keep it if it's small (specific)
    }, $content, -1, $count);
    
    if ($count > 0) {
        // also, if it's not including header.php but has the hardcoded header/sidebar, we should probably warn or try to fix.
        // For now, let's just save the CSS fix.
        file_put_contents($file, $content);
        echo "Removed huge style block from $file\n";
    }
}
?>
