<?php
$const_prefix = '_MI_' . strtoupper($module_dirname);

$lang_dir = dirname(__FILE__) . '/language/';
if (file_exists($lang_file = $lang_dir . @$xoopsConfig['language'] . '/modinfo.php')
     || file_exists($lang_file = $lang_dir . 'english/modinfo.php')) {
    include $lang_file;
}

$modversion['name'] = constant($const_prefix . '_NAME');
$modversion['version'] = 1.10;
$modversion['description'] = constant($const_prefix . '_DESC');
$modversion['credits'] = 'Kazumi Ono';
$modversion['author'] = 'Kazumi Ono AKA onokazu';
$modversion['help'] = '';
$modversion['license'] = 'GPL';
$modversion['official'] = 0;
$modversion['image'] = 'logo.png';
$modversion['dirname'] = $module_dirname;

//Admin
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin_menu.php';

// Menu
$modversion['hasMain'] = 1;
if (is_object(@$GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') == $module_dirname) {
    require dirname(__FILE__) . '/include/common.php';
    if ($plugg_active_menu_items = $plugg->getPlugin('XOOPSCube')->getConfig('menu_items')) {
        foreach ($plugg->getPlugin('XOOPSCube')->getMenuItems() as $plugg_menu_path => $plugg_menu_title) {
            if (!in_array('@all', $plugg_active_menu_items) // all items allowed?
                && !in_array($plugg_menu_path, $plugg_active_menu_items)
            ) continue;
        
            $modversion['sub'][] = array(
                'name' => $plugg_menu_title,
                'url' => '?' . $plugg->getRouteParam() . '=' . urlencode($plugg_menu_path),
            );
        }
    }
}

// Search
$modversion['hasSearch'] = 0;

// Module administration callbacks
$modversion['onInstall'] = 'admin_module.php' ;
$modversion['onUpdate'] = 'admin_module.php' ;
$modversion['onUninstall'] = 'admin_module.php' ;

// Blocks
// List current blocks during module update to prevent from being deleted
if (!empty($_POST['dirname'])
    && is_object(@$GLOBALS['xoopsModule'])
    && $GLOBALS['xoopsModule']->getVar('dirname') == 'legacy'
    && $_POST['dirname'] == $module_dirname
    && @$_REQUEST['action'] == 'ModuleUpdate'
) {
    $plugg_blocks = xoops_gethandler('block')->getObjectsDirectly(new Criteria('dirname', $module_dirname));
    foreach (array_keys($plugg_blocks) as $i) {
        $plugg_block_func_num = $plugg_blocks[$i]->get('func_num');
        $modversion['blocks'][$plugg_block_func_num] = array(
            'func_num' => $plugg_block_func_num,
            'file' => $plugg_blocks[$i]->get('func_file'),
            'name' => $plugg_blocks[$i]->get('name'),
            'description' => '',
            'show_func' => $plugg_blocks[$i]->get('show_func'),
            'edit_func' => $plugg_blocks[$i]->get('edit_func'),
            'options' => $plugg_blocks[$i]->get('options'),
            'template' => $plugg_blocks[$i]->get('template'),
        );
    }
    unset($plugg_blocks, $plugg_block_func_num);
}