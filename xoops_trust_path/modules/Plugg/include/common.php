<?php
set_include_path(XOOPS_TRUST_PATH . '/modules/Plugg/library' . PATH_SEPARATOR . get_include_path());
require_once 'Plugg.php';
require_once 'SabaiXOOPS.php';

if (!Plugg::started()) {
    Plugg::start(
        _CHARSET,
        _LANGCODE,
        XOOPS_TRUST_PATH . '/modules/Plugg/locales',
        XOOPS_TRUST_PATH . '/modules/Plugg/plugins',
        XOOPS_TRUST_PATH . '/modules/Plugg/cache',
        false
    );
}

if (!Plugg::exists($module_dirname)) {
    if (!isset($module)) {
        $module = SabaiXOOPS::isInModule($module_dirname) ? $GLOBALS['xoopsModule'] : xoops_gethandler('module')->getByDirname($module_dirname);
    }
    $plugg = Plugg::create(
        $module_dirname,
        $module ? $module->getVar('name') : constant('_MI_' . strtoupper($module_dirname) . '_NAME'),
        XOOPS_URL . '/modules/' . $module_dirname,
        array(
            'siteName' => $GLOBALS['xoopsConfig']['sitename'],
            'siteEmail' => $GLOBALS['xoopsConfig']['adminmail'],
            'siteUrl' => XOOPS_URL,
            'dbScheme' => XOOPS_DB_TYPE,
            'dbOptions' => array(
                'host' => XOOPS_DB_HOST,
                'dbname' => XOOPS_DB_NAME,
                'user' => XOOPS_DB_USER,
                'pass' => XOOPS_DB_PASS,
                'charset' => (strpos(XOOPS_VERSION, '2.0.', 1) || (defined('LEGACY_JAPANESE_ANTI_CHARSETMYSQL') && LEGACY_JAPANESE_ANTI_CHARSETMYSQL)) ? null : _CHARSET,
            ),
            'dbTablePrefix' => XOOPS_DB_PREFIX . '_' . strtolower($module_dirname) . '_' ,
        ),
        Plugg::XOOPSCUBE_LEGACY
    )->initialize()->setScript('admin', 'admin/index.php')->setScript('image', 'image.php')
        ->setScript('css', 'css.php')->setScript('js', 'js.php');
} else {
    $plugg = Plugg::get($module_dirname);
}