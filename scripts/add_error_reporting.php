<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Recursively add error reporting lines to PHP files missing them.
// Usage: php scripts/add_error_reporting.php

$root = realpath(__DIR__ . '/..');
$skipDirs = ['.git', 'node_modules', 'vendor', 'assets/uploads'];
$needlePatterns = [
    "ini_set('display_errors'",
    'ini_set("display_errors"',
    "ini_set('display_startup_errors'",
    'error_reporting(E_ALL)'
];
$insertBlock = "ini_set('display_errors', 1);\nini_set('display_startup_errors', 1);\nerror_reporting(E_ALL);\n\n";

function shouldSkip($path, $skipDirs) {
    foreach ($skipDirs as $d) {
        if (strpos($path, DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR) !== false) return true;
        if (substr($path, -strlen(DIRECTORY_SEPARATOR . $d)) === DIRECTORY_SEPARATOR . $d) return true;
    }
    return false;
}

$modified = 0;
$checked = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $fileinfo) {
    if ($fileinfo->isDir()) continue;
    $filePath = $fileinfo->getPathname();
    if (shouldSkip($filePath, $skipDirs)) continue;
    if (strtolower($fileinfo->getExtension()) !== 'php') continue;

    $checked++;
    $content = file_get_contents($filePath);
    $hasAny = false;
    foreach ($needlePatterns as $pat) {
        if (strpos($content, $pat) !== false) { $hasAny = true; break; }
    }
    if ($hasAny) continue;

    // Find first <?php tag
    $pos = strpos($content, "<?php");
    if ($pos === false) {
        // skip files without opening tag
        continue;
    }

    $insertPos = $pos + 5; // right after <?php
    // If next char is newline, keep formatting
    $newContent = substr($content, 0, $insertPos) . "\n" . $insertBlock . substr($content, $insertPos);

    // Backup original file
    copy($filePath, $filePath . '.bak');
    file_put_contents($filePath, $newContent);
    echo "Updated: $filePath\n";
    $modified++;
}

echo "\nChecked: $checked PHP files. Modified: $modified files.\n";
echo "Backups created with .bak suffix for each modified file.\n";

?>