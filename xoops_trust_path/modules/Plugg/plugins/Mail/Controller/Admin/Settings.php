<?php
class Plugg_Mail_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $mailer_options = array();
        foreach ($this->getPluginManager()->getInstalledPluginsByInterface('Plugg_Mail_Mailer') as $plugin_name => $plugin_nicename) {
            if (!$mailer_plugin = $this->getPlugin($plugin_name)) continue;
            $mailer_options[$plugin_name] = array(
                'title' => h($mailer_plugin->mailGetNicename()),
                'summary' => '',
                'links' => $mailer_plugin->mailGetSettings() ? $this->LinkTo($this->_('Configure'), $this->getUrl('/system/settings/mail/mailer/' . $plugin_name)) : '',
            );
        }

        $form = array(
            'Mail' => array(
                '#type' => 'fieldset',
                '#tree' => true,
                'mailerPlugin' => array(
                    '#title' => $this->_('Mailer library'),
                    '#description' => $this->_('Select the default mailer library to be used to send mail.'),
                    '#type' => 'tableselect',
                    '#options' => $mailer_options,
                    '#header' => array('title' => $this->_('Title'), 'summary' => $this->_('Summary'), 'links' => ''),
                    '#default_value' => $this->getPlugin()->getConfig('mailerPlugin'),
                    '#required' => true,
                )
            )
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Mail'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Mail'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}