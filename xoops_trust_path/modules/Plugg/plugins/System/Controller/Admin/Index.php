<?php
class Plugg_System_Controller_Admin_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $status = array();

        $report_plugins = $this->getPluginManager()->getInstalledPluginsByInterface('Plugg_System_Report');

        $default_status = array('severity' => Plugg_System_Plugin::STATUS_SEVERITY_OK);

        foreach (array_keys($report_plugins) as $plugin_name) {
            if (!$plugin = $this->getPlugin($plugin_name)) continue;

            foreach ($plugin->systemReportGetNames() as $report_name) {
                $status[] = array_merge(
                    $default_status,
                    $plugin->systemReportGetStatus($report_name)
                );
            }
        }
        usort($status, array(__CLASS__, '_sort'));
       
        $response->setContent($this->RenderTemplate('system_admin_index', array('status' => $status)));
    }

    private static function _sort($a, $b) {
        if (isset($a['weight'])) {
            if (isset($b['weight'])) return $a['weight'] - $b['weight'];

            return $a['weight'];
        }

        return isset($b['weight']) ? -$b['weight'] : strcmp($a['title'], $b['title']);
    }
}