<?php
require dirname(__FILE__) . '/include/common.php';

// Create the following file and define the PLUGG_CRON_PHP_SAPI_NAME constant
// with the name of command line interface of your system if it is other
// than the default "cli"
@include_once dirname(__FILE__) . '/include/cron_php_sapi_name.php';

if (!defined('PLUGG_CRON_PHP_SAPI_NAME')) {
    define('PLUGG_CRON_PHP_SAPI_NAME', 'cli');
}

// Make sure the request is coming from the command line
if (php_sapi_name() !== PLUGG_CRON_PHP_SAPI_NAME) exit;

$logs = $plugg->runCron();

// Print out logs
echo implode(PHP_EOL, $logs) . PHP_EOL;