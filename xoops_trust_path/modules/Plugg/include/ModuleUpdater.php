<?php
require_once 'SabaiXOOPS/ModuleInstaller.php';

class Plugg_Application_XOOPSCubeLegacy_ModuleUpdater extends SabaiXOOPS_ModuleInstaller
{
    private $_app;
    private $_lastVersion;

    public function __construct(Sabai_Application $app, $lastVersion)
    {
        parent::__construct('Legacy.Admin.Event.ModuleUpdate.%s.Success', 'Legacy.Admin.Event.ModuleUpdate.%s.Fail', 'msgs');
        $this->_app = $app;
        $this->_lastVersion = intval($lastVersion);
    }

    protected function _doExecute($module)
    {
        // Create db instance for xoops
        $db = $this->_app->getLocator()->createService('DB', array(
            'tablePrefix' => XOOPS_DB_PREFIX . '_'
        ));

        // Check if upgrading from 1.00
        if ($this->_lastVersion == 100 && $module->getVar('version') > 100) {
            $sql = sprintf(
                'DELETE FROM %snewblocks WHERE options LIKE %s',
                $db->getResourcePrefix(),
                $db->escapeString('Plugg|Plugg%')
            );
            if (!$db->exec($sql, false)) {
                $this->addLog('Failed updating tables. Please manually execute the following SQL: ' . $sql);
                return false;
            }

            $this->addLog('Module updated from 1.00 to 1.01.');
        }

        // Delete deprecated config settings if upgrading from older than 1.04
        if ($this->_lastVersion < 104) {
            $sql = sprintf(
                'DELETE FROM %sconfig WHERE conf_modid = %d AND conf_name LIKE %s',
                $db->getResourcePrefix(),
                $module->getVar('mid'),
                $db->escapeString('site%')
            );
            if (!$db->exec($sql, false)) {
                $this->addLog('Failed updating tables. Please manually execute the following SQL: ' . $sql);
                return false;
            }

            $this->addLog('Module updated to 1.04.');
        }

        return true;
    }
}