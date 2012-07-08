<?php
if (!@constant('XOOPS_TRUST_PATH')) {
    die('XOOPS_TRUST_PATH must be defined in mainfile.php and must be a path to a valid directory');
}
$module_dirname = basename(dirname(__FILE__));