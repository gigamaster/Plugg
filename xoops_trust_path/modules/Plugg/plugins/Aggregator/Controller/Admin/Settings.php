<?php
class Plugg_Aggregator_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'Aggregator' => array(
                '#tree' => true,
                'default' => array(
                    '#title' => $this->_('Feed default settings'),
                    '#description' => $this->_('Configure the default settings for newly added feeds. The settings may be overridden by individual feeds.'),
                    'allowImage' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow image tags in feed items.'),
                        '#description' => $this->_('Check this option to enable image tags in feed items. For security reasons, it is highly recommended that you disable this feature if accepting feeds from untrusted users.'),
                        '#default_value' => (null !== $default = $this->getPlugin()->getConfig('default', 'allowImage')) ? $default : 0,
                    ),
                    'allowExternalResources' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow external resources in feed items.'),
                        '#description' => $this->_('Check this option to allow resources hosted outside the feed website to be displayed in feed items, for example external website images. For security reasons, it is highly recommended that you disable this feature if accepting feeds from untrusted users.'),
                        '#default_value' => $this->getPlugin()->getConfig('default', 'allowExternalResources'),
                    ),
                    'authorPref' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Default display preference of feed item author'),
                        '#description' => $this->_('Select how the author of each feed item should be displayed by default.'),
                        '#options' => array(
                            Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_ENTRY_AUTHOR => $this->_('Display the author name of each feed item if available. Otherwise, display the feed owner username.'),
                            Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_BLOG_OWNER => $this->_('Always display the feed owner username as the author.')
                        ),
                        '#delimiter' => '<br />',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('default', 'authorPref'),
                    ),
                ),
                'cronIntervalDays' => array(
                    '#type' => 'radios',
                    '#title' => $this->_('Feed update check interval'),
                    '#description' => $this->_('This setting enables you to control how often (in number of days) feeds are checked for new updates.'),
                    '#options' => array(
                        0 => $this->_('Never'), 1 => 1, 2 => 2, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 30 => 30
                    ),
                    '#delimiter' => '&nbsp;',
                    '#required' => true,
                    '#default_value' => $this->getPlugin()->getConfig('cronIntervalDays'),
                ),
                'feedsRequireApproval' => array(
                    '#title' => $this->_('Feeds require approval.'),
                    '#description' => $this->_('Check this option (recommended) to always require administrator approval for user submitted feeds.'),
                    '#required' => true,
                    '#type' => 'checkbox',
                    '#default_value' => $this->getPlugin()->getConfig('feedsRequireApproval'),
                ),
            ),
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Aggregator'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Aggregator'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}