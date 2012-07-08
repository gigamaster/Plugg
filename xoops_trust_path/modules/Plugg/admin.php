<?php
require dirname(__FILE__) . '/include/common.php';
require 'SabaiXOOPS/cp_header.inc.php';
require dirname(__FILE__) . '/include/Filter.php';

$controller = new Plugg_AdminController();
$controller->prependFilter(new Sabai_Handle_Instance(new Plugg_Application_XOOPSCubeLegacy_Filter()));

$plugg->setCurrentScriptName('admin')
    ->run($controller, new Plugg_Request())
    ->setLayoutUrl(XOOPS_URL . '/modules/' . $module_dirname . '/layouts/default')
    ->setLayoutDir(XOOPS_ROOT_PATH . '/modules/' . $module_dirname . '/layouts/default')
    ->setLayoutFile('admin')
    ->send();