<?php
class Plugg_User_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $manager_options = array();
        foreach ($this->getPluginManager()->getInstalledPluginsByInterface('Plugg_User_Manager') as $plugin_name => $plugin_nicename) {
            if (!$manager_plugin = $this->getPlugin($plugin_name)) continue;
            $manager_options[$plugin_name] = array(
                'title' => h($plugin_nicename),
                'summary' => '',
                'links' => $manager_plugin->userGetManagerSettings() ? $this->LinkTo($this->_('Configure'), $this->getUrl('/user/settings/manager/' . $plugin_name)) : '',
            );
        }
        
        $form = array(
            'userManagerPlugin' => array(
                '#title' => $this->_('User management plugin'),
                '#description' => $this->_('IMPORTANT! If you are switching to another plugin, make sure that the new plugin already has valid user accounts. Otherwise, you may not be able to login and/or may result in corruputed user system.'),
                '#type' => 'tableselect',
                '#options' => $manager_options,
                '#header' => array('title' => $this->_('Title'), 'summary' => $this->_('Summary'), 'links' => ''),
                '#default_value' => $this->getPlugin()->getConfig('userManagerPlugin'),
                '#required' => true,
            ),
            'user_privacy' => array(
                '#type' => 'fieldset',
                '#collapsible' => true,
                '#title' => $this->_('Privacy settings'),
                'allowViewAnyUser' => array(
                    '#title' => $this->_('Allow anyone including guest users to view any user profile.'),
                    '#type' => 'checkbox',
                    '#default_value' => $this->getPlugin()->getConfig('allowViewAnyUser'),
                ),
            ),
            'user_registration' => array(
                '#type' => 'fieldset',
                '#collapsible' => true,
                '#title' => $this->_('Registration settings'),
                'allowRegistration' => array(
                    '#title' => $this->_('Allow new user registration.'),
                    '#type' => 'checkbox',
                    '#default_value' => $this->getPlugin()->getConfig('allowRegistration'),
                ),
                'userActivation' => array(
                    '#title' => $this->_('Select activation type of newly registered users'),
                    '#required' => true,
                    '#type' => 'radios',
                    '#default_value' => $this->getPlugin()->getConfig('userActivation'),
                    '#options' => array(
                        'user' => $this->_('Require activation by user'),
                        'auto' => $this->_('Activate automatically'),
                        'admin' => $this->_('Activation by administrators'),
                    ),
                ),
            ),
            'user_autologin' => array(
                '#type' => 'fieldset',
                '#collapsible' => true,
                '#title' => $this->_('Autologin settings'),
                'enableAutologin' => array(
                    '#title' => $this->_('Allow users to keep logged in for specific range of time.'),
                    '#default_value' => $this->getPlugin()->getConfig('enableAutologin'),
                    '#type' => 'checkbox',
                ),
                'autologinSessionLifetime' => array(
                    '#title' => $this->_('Number of days users can keep logged in'),
                    '#default_value' => $this->getPlugin()->getConfig('autologinSessionLifetime'),
                    '#size' => 5,
                ),
                'limitSingleAutologinSession' => array(
                    '#title' => $this->_('Limit only one autologin session to be created per user.'),
                    '#default_value' => $this->getPlugin()->getConfig('limitSingleAutologinSession'),
                    '#type' => 'checkbox',
                ),
            ),
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values)) {
            if (!$this->getPlugin()->saveConfig($form->values)) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}