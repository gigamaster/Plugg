<?php
class Plugg_Cron_Controller_Admin_System_Settings extends Plugg_Form_Controller
{
    private $_logs;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_submitButtonLabel = $this->_('Run cron now');
        $this->_cancelUrl = null;
        
        return array();
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_logs = $this->_application->runCron(0); // Force run cron
        
        return false; // show form again withot redirect
    }
    
    public function viewForm(Plugg_Form_Form $form, &$formHtml, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($this->_logs)) {
            $formHtml = $formHtml . PHP_EOL . '<div><code><pre>' . h(implode(PHP_EOL, $this->_logs)) . '</pre></code>';
        }
    }
}