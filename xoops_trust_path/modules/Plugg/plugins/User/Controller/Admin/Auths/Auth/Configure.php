<?php
class Plugg_User_Controller_Admin_Auths_Auth_Configure extends Plugg_Form_Controller
{
    private $_noCache = array();

    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        return array(
            '#id' => 'user_admin_auth_' . strtolower($this->auth->plugin),
            $this->auth->plugin => array_merge(
                $this->getPlugin($this->auth->plugin)->userAuthGetSettings(),
                array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                    '#tree_allow_override' => false,
                )
            )
        );
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $config_cacheable = $config_noncacheable = array();
        if ($config = @$form->values[$this->auth->plugin]) {
            foreach ($config as $k => $v) {
                if (!in_array($k, $this->_noCache)) {
                    $config_cacheable[$k] = $v;
                } else {
                    $config_noncacheable[$k] = $v;
                }
            }
        }

        if (!$this->getPlugin($this->auth->plugin)->saveConfig($config_cacheable, $config_noncacheable)) {
            return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}