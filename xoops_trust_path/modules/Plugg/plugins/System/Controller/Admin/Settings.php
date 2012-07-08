<?php
class Plugg_System_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $site_url_arr = parse_url($this->createUrl(array('base' => '', 'script' => 'main')));
        $site_url = $site_url_arr['scheme'] . '://' . $site_url_arr['host'] . $site_url_arr['path'];
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');
        
        return array(
            'System' => array(
                '#tree' => true,
                'front_page' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Front page settings'),
                    'path' => array(
                        '#title' => $this->_('Front page URL'),
                        '#description' => $this->_('The front page (or home page) displays content from this URL. If unsure, specify "widgets".'),
                        '#type' => 'textfield',
                        '#field_prefix' => rtrim($site_url, '/') . '/',
                        '#size' => 30,
                        '#default_value' => $this->getPlugin()->getConfig('front_page', 'path'),
                        '#required' => true,
                    ),
                    'show_title' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Display title at the top.'),
                        '#description' => sprintf($this->_('Check this option to display the application title "%s" at the top of front page.'), $this->getTitle()),
                        '#default_value' => $this->getPlugin()->getConfig('front_page', 'show_title'),
                    ),
                ),
                'display' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Display settings'),
                    'breadcrumbs' => array(
                        '#collapsible' => true,
                        '#title' => $this->_('Breadcrumb settings'),
                        '#tree' => true,
                        'show' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Display page breadcrumbs on all pages.'),
                            '#default_value' => $this->getPlugin()->getConfig('display', 'breadcrumbs', 'show'),
                        ),
                        'show_full_path' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Display full path from the website homepage.'),
                            '#default_value' => $this->getPlugin()->getConfig('display', 'breadcrumbs', 'show_full_path'),
                        )
                    ),
                ),
                'debug' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Debug settings'),
                    '#tree' => true,
                    '#description' => $this->_('If debug is enabled, various system messages will be displayed on the screen. Make sure that no other users can visit the site when this option is enabled, as some messages contain sensitive information.'),
                    'level' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Debug messages to display'),
                        '#options' => array(
                            Sabai_Log::NONE => $this->_('None'),
                            Sabai_Log::ERROR | Sabai_Log::WARN => $this->_('Error and warning messages'),
                            Sabai_Log::ALL => $this->_('All messages'),
                        ),
                        '#default_value' => $this->getPlugin()->getConfig('debug', 'level'),
                    ),
                    'output' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Output debug messages as:'),
                        '#options' => array(
                            'html' => $this->_('HTML on the screen'),
                            'firebug' => $this->_('Firebug console messages (requires FirePHP installed)'),
                        ),
                        '#options_disabled' => array('firebug'), // not yet supported
                        '#default_value' => $this->getPlugin()->getConfig('debug', 'output'),
                    )
                )
            )
        );
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['System'])) {
            if (!$this->getPlugin()->saveConfig($form->values['System'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}