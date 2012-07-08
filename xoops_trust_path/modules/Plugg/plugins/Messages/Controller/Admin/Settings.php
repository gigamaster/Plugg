<?php
class Plugg_Messages_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        return array(
            'Messages' => array(
                '#tree' => true,
                'deleteOlderThanDays' => array(
                    '#type' => 'radios',
                    '#required' => true,
                    '#title' => $this->_('Purge messages'),
                    '#description' => $this->_('Periodically delete messages without a star and older than following days:'),
                    '#options' => array(0 => $this->_('Never'), 5 => 5, 10 => 10, 30 => 30, 50 => 50, 100 => 100, 365 => 365),
                    '#delimiter' => '&nbsp;',
                    '#default_value' => $this->getPlugin()->getConfig('deleteOlderThanDays'),
                ),
            ),
        );
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Messages'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Messages'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}