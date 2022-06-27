<?php

if (php_sapi_name() != "cli") {
    die("This script must run from cli");
}

$files = [];
$files[] = "https://cdn.jsdelivr.net/npm/jodit@3.18.9/build/jodit.es2018.min.js";
$files[] = "https://cdn.jsdelivr.net/npm/jodit@3.18.9/build/jodit.es2018.min.css";

// $bundleCss = '';
// $bundleJs = '';

$baseDir = dirname(__DIR__);
$dest = $baseDir . "/client/cdn";

foreach ($files as $file) {
    $baseFile = str_replace("https://cdn.jsdelivr.net/npm/jodit@3.18.9/build/", "", $file);
    $parts = explode("/", $baseFile);
    $destFolder = $dest . "/" . dirname($baseFile);
    if (!is_dir($destFolder)) {
        mkdir($destFolder, 0755, true);
    }
    $destFile = $destFolder . "/" . basename($baseFile);

    $contents = file_get_contents($file);
    if (!$contents) {
        throw new Exception("Failed to download $file");
    }
    // $ext = pathinfo($file, PATHINFO_EXTENSION);
    // if ($ext == "js") {
    //     $bundleJs .= $contents;
    // } else {
    //     $bundleCss .= $contents;
    // }

    // Remove sourcemap
    $base = basename($file);
    $contents = str_replace("/*# sourceMappingURL=$base.map */", "", $contents);

    file_put_contents($destFile, $contents);
    echo "Copied $file to $destFile\n";
}
// file_put_contents(dirname($dest) . "/bundle.css", $bundleCss);
// file_put_contents(dirname($dest) . "/bundle.js", $bundleJs);
// echo "Created bundles\n";
