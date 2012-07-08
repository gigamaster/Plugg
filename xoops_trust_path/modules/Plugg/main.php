<?php
require dirname(__FILE__) . '/include/common.php';
require dirname(__FILE__) . '/include/Filter.php';

$controller = new Plugg_MainController();
$controller->prependFilter(new Sabai_Handle_Instance(new Plugg_Application_XOOPSCubeLegacy_Filter()));

$plugg->run($controller, new Plugg_Request())->send();