<?php
abstract class Plugg_XOOPSCube_Module
{
    protected $_module, $_moduleDir, $_moduleDocRoot, $_moduleSrcRoot;

    public function __construct($module)
    {
        $this->_module = $module;
        $this->_moduleDir = $module->getVar('dirname');
        $this->_moduleDocRoot = XOOPS_ROOT_PATH . '/modules/' . $this->_moduleDir;
        $this->_moduleSrcRoot = XOOPS_TRUST_PATH . '/modules/' . $this->_moduleDir;
    }
    
    public function getModule()
    {
        return $this->_module;
    }
    
    public function getModuleVar($var)
    {
        return $this->_module->getVar($var);
    }
}