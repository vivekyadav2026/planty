<?php
/**
 * Upload this file to your live server's public/ folder (e.g. public_html/server_fix.php)
 * Then visit: https://yourdomain.com/server_fix.php
 */

$baseDir = dirname(__DIR__); // Assumes this is in public/, so base is one level up

$directories = [
    $baseDir . '/storage',
    $baseDir . '/storage/framework',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/logs',
    $baseDir . '/bootstrap/cache',
];

echo "<h3>Laravel Server Fixer</h3>";

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "<span style='color:green;'>Created: " . basename($dir) . "</span><br>";
        } else {
            echo "<span style='color:red;'>Failed to create: " . basename($dir) . "</span><br>";
        }
    } else {
        echo "<span style='color:blue;'>Exists: " . basename($dir) . "</span><br>";
    }
    
    // Attempt to set permissions
    @chmod($dir, 0775);
}

// Clear cached config if it exists
$configCache = $baseDir . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    @unlink($configCache);
    echo "<span style='color:orange;'>Deleted old config cache.</span><br>";
}

echo "<h4>Done! Now try refreshing your main website.</h4>";
echo "<p>Note: Once your site is working, please delete this server_fix.php file for security.</p>";
