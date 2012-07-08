<?php
if (!isset($_GET['plugin'])
    || (!$plugin = preg_replace('/[^0-9a-zA-Z]/', '', $_GET['plugin']))
) {
    exit;
}
if (!isset($_GET['file'])
    || (!$file = preg_replace('#[^0-9a-zA-Z_\-\.]#', '', $_GET['file']))
    || !preg_match('/\.js$/', $file)
) {
    exit;
}
$js_dir = 'js';
if (isset($_GET['dir'])
    && ($dir = preg_replace('/[^0-9a-zA-Z_\-]/', '', $_GET['dir']))
) {
    $js_dir = $dir;
}

$dirname = str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__));
$file_path = realpath(sprintf('%s/plugins/%s/%s/%s', $dirname, $plugin, $js_dir, $file));
if (!$file_path) exit;

$file_path = str_replace(DIRECTORY_SEPARATOR, '/', $file_path);
if (strpos($file_path, $dirname) !== 0) exit;

$cache_limit = 432000; // 5 days
if (!$file_mtime = filemtime($file_path)) $file_mtime = time();

header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $cache_limit));
header('Cache-Control: public, max-age=' . $cache_limit);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $file_mtime));
header('Content-Type: application/x-javascript');

echo file_get_contents($file_path);