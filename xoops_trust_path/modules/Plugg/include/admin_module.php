<?php
eval("
function xoops_module_install_{$module_dirname}(\$module)
{
    \$module_dirname = '{$module_dirname}';
    require dirname(__FILE__) . '/common.php';
    require_once dirname(__FILE__) . '/ModuleInstaller.php';
    \$installer = new Plugg_Application_XOOPSCubeLegacy_ModuleInstaller(\$plugg);
    return \$installer->execute(\$module);
}

function xoops_module_uninstall_{$module_dirname}(\$module)
{
    \$module_dirname = '{$module_dirname}';
    require dirname(__FILE__) . '/common.php';
    require_once dirname(__FILE__) . '/ModuleUninstaller.php';
    \$uninstaller = new Plugg_Application_XOOPSCubeLegacy_ModuleUninstaller(\$plugg, \$module->getVar('version'));
    return \$uninstaller->execute(\$module);
}

function xoops_module_update_{$module_dirname}(\$module, \$version)
{
    \$module_dirname = '{$module_dirname}';
    require dirname(__FILE__) . '/common.php';
    require_once dirname(__FILE__) . '/ModuleUpdater.php';
    \$updater = new Plugg_Application_XOOPSCubeLegacy_ModuleUpdater(\$plugg, \$version);
    return \$updater->execute(\$module);
}
");