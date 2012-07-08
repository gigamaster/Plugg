<?php
class Plugg_Filters_Controller_Admin_System_Settings_Filter_Configure extends Plugg_Form_Controller
{
    private $_noCache = array();

    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            '#id' => 'filters_admin_settings_filter_' . strtolower($this->filter->plugin),
            $this->filter->plugin => array_merge(
                $filter_plugin_settings = $this->getPlugin($this->filter->plugin)->filtersFilterGetSettings($this->filter->name),
                array('#tree' => true, '#tree_allow_override' => false)
            ),
        );

        foreach ($filter_plugin_settings as $k => $data) {
            if (isset($data['#cacheable']) && $data['#cacheable'] === false) {
                $this->_noCache[] = $k;
            }
        }
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $config_cacheable = $config_noncacheable = array();
        if ($config = $form->values[$this->filter->plugin]) {
            foreach ($config as $k => $v) {
                if (!in_array($k, $this->_noCache)) {
                    $config_cacheable[$k] = $v;
                } else {
                    $config_noncacheable[$k] = $v;
                }
            }
        }

        if (!$this->getPlugin($this->filter->plugin)->saveConfig($config_cacheable, $config_noncacheable)) {
            return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}