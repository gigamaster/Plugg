<?php
$const_prefix = '_MI_' . strtoupper($module_dirname);
$adminmenu[1]['title'] = 'Dashboard';
$adminmenu[1]['link'] = 'admin/';
$adminmenu[2]['title'] = 'System management';
$adminmenu[2]['link'] = 'admin/?q=/system';
$adminmenu[3]['title'] = 'User management';
$adminmenu[3]['link'] = 'admin/?q=/user';
$adminmenu[4]['title'] = 'Group management';
$adminmenu[4]['link'] = 'admin/?q=/groups';
$adminmenu[5]['title'] = 'Content management';
$adminmenu[5]['link'] = 'admin/?q=/content';
$adminmenu[6]['title'] = constant($const_prefix . '_ADMENU_XROLES');
$adminmenu[6]['link'] = 'admin/roles.php';
