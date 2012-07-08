<?php
require dirname(__FILE__) . '/include/common.php';
require 'SabaiXOOPS/cp_header.inc.php';
require dirname(__FILE__) . '/include/AdminRoles.php';
require dirname(__FILE__) . '/include/Filter.php';

// Include admin language file
if (!empty($xoopsConfig['language']) &&
    $xoopsConfig['language'] != 'english' &&
    file_exists($language_file = XOOPS_TRUST_PATH . '/modules/Plugg/language/' . $xoopsConfig['language'] . '/admin.php')
) {
    include_once $language_file;
} else {
    // fallback english
    include_once XOOPS_TRUST_PATH . '/modules/Plugg/language/english/admin.php';
}

$controller = new Plugg_Application_XOOPSCubeLegacy_AdminRoles($xoopsModule);
$controller->prependFilter(new Sabai_Handle_Instance(new Plugg_Application_XOOPSCubeLegacy_Filter()));

$plugg->setScript('admin_roles', 'admin/roles.php')
    ->setCurrentScriptName('admin_roles')
    ->run($controller, new Plugg_Request())
    ->setLayoutUrl(XOOPS_URL . '/modules/' . $module_dirname . '/layouts/default')
    ->setLayoutDir(XOOPS_ROOT_PATH . '/modules/' . $module_dirname . '/layouts/default')
    ->setLayoutFile('admin')
    ->send();