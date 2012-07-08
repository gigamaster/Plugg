<?php
error_reporting(0);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// We need absolute path here since the current directory may be different
require dirname(__FILE__) . '/../../mainfile.php';
require dirname(__FILE__) . '/common.php';
require XOOPS_TRUST_PATH . '/modules/Plugg/cron.php';