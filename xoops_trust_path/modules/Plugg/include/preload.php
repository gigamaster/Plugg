<?php
require_once dirname(__FILE__) . '/Preloader.php';

eval('
class ' . ucfirst($module_dirname) . '_xoopscube_preload extends XCube_ActionFilter
{
    private $_preloader;

    public function __construct($controller)
    {
        parent::XCube_ActionFilter($controller);
        $this->_preloader = new Plugg_Application_XOOPSCubeLegacy_Preloader("' . $module_dirname . '");
    }

    public function preBlockFilter()
    {
        $this->_preloader->preFilter($this->mRoot);
    }

    public function postFilter()
    {
        $this->_preloader->postFilter($this->mRoot);
    }
}
');