<?php
// Define the root directory to search in
$rootDir = __DIR__ . '/app/views';

// Function to recursively search for PHP files
function findPhpFiles($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, findPhpFiles($path));
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $result[] = $path;
        }
    }
    
    return $result;
}

// Function to update files
function updateFile($filePath) {
    $content = file_get_contents($filePath);
    
    // Replace APPROOT . '/views/ with VIEWSPATH . '/
    $updatedContent = str_replace(
        "APPROOT . '/views/", 
        "VIEWSPATH . '/", 
        $content
    );
    
    if ($content !== $updatedContent) {
        file_put_contents($filePath, $updatedContent);
        echo "Updated: $filePath\n";
    }
}

// Find all PHP files
$phpFiles = findPhpFiles($rootDir);

// Update each file
foreach ($phpFiles as $phpFile) {
    updateFile($phpFile);
}

echo "All view files have been updated.\n";
?> 