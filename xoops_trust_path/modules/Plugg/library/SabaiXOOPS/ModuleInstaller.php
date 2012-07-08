<?php
abstract class SabaiXOOPS_ModuleInstaller
{
    /**
     * @access private
     * @var string
     */
    private $_xclSuccessEventFormat;
    /**
     * @access private
     * @var string
     */
    private $_xclFailEventFormat;
    /**
     * @access private
     * @var string
     */
    private $_x2LogVar;
    /**
     * @access private
     * @var array
     */
    private $_logs = array();

    /**
     * Constructor
     *
     * @param string $xclSuccessEventFormat
     * @param string $xclFailEventFormat
     * @param string $x2LogVar
     * @return SabaiXOOPS_ModuleInstaller
     */
    public function __construct($xclSuccessEventFormat = 'Legacy.Admin.Event.ModuleInstall.%s.Success', $xclFailEventFormat = 'Legacy.Admin.Event.ModuleInstall.%s.Fail', $x2LogVar = 'ret')
    {
        $this->_xclSuccessEventFormat = $xclSuccessEventFormat;
        $this->_xclFailEventFormat = $xclFailEventFormat;
        $this->_x2LogVar = $x2LogVar;
    }

    /**
     * Executes the installer
     *
     * @param XoopsModule $module
     * @return bool
     */
    function execute($module)
    {
        $result = $this->_doExecute($module);
        if (defined('XOOPS_CUBE_LEGACY')) {
            $dirname = ucfirst($module->getVar('dirname'));
            $root = XCube_Root::getSingleton();
            $root->mDelegateManager->add(sprintf($this->_xclSuccessEventFormat, $dirname), array($this, 'reportLogs'));
            $root->mDelegateManager->add(sprintf($this->_xclFailEventFormat, $dirname), array($this, 'reportLogs'));
        } else {
            $GLOBALS[$this->_x2LogVar][] = '<code>' . implode('<br />', $this->_logs) . '</code><br />';
        }
        return $result;
    }

    /**
     * Adds a log message
     *
     * @param string $msg
     */
    public function addLog($msg)
    {
        $this->_logs[] = !is_string($msg) ? implode(PHP_EOL, (array)$msg) : $msg;
    }

    /**
     * Reports logs back to the XCL core
     *
     * @param XoopsModule $module
     * @param Legacy_ModuleInstallLog $log
     */
    public function reportLogs($module, $log)
    {
        foreach ($this->_logs as $msg) {
            $log->add($msg);
        }
    }

    /**
     * Executes the installer
     *
     * @abstract
     * @access protected
     * @param XoopsModule $module
     * @return bool
     */
    abstract protected function _doExecute($module);
}