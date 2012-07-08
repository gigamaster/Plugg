<?php
$const_prefix = '_MI_' . strtoupper($module_dirname);

if (!defined($const_prefix)) {
    define($const_prefix, 1);

    define($const_prefix . '_NAME', 'Community');
    define($const_prefix . '_DESC', 'Plugg module for XOOPS powered by Sabai Framework');

    // Admin menu
    define($const_prefix . '_ADMENU_XROLES', 'Assign roles by group');
}