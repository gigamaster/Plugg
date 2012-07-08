<?php
class Plugg_Aggregator_Model_FeedForm extends Plugg_Aggregator_Model_Base_FeedForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $config = $this->_model->PluginConfig('default', 'Aggregator');
        $settings = parent::getSettings($entity);

        // remove user id form element by default
        unset($settings['user_id']);

        $owner = '';
        if ($entity->user_id) {
            $user = $this->_model->User_Identity($entity->user_id);
            if (!$user->isAnonymous()) $owner = $user->username;
        }
        $settings['owner'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Feed owner'),
            '#description' => $this->_model->_('Enter the username of feed owner. Leave it blank to add a system owned feed.'),
            '#size' => 30,
            '#maxlength' => 255,
            '#element_validate' => array(array($this, 'validateUser')),
            '#default_value' => $owner,
        );
        //$this->insertElementAfter($owner, 'feed_url');

        $settings['favicon_url']['#type'] = 'url';
        $settings['favicon_url']['#title'] = $this->_model->_('Favicon URL');
        $settings['favicon_url']['#description'] = $this->_model->_('Enter the URL of website favicon image. Leave it blank to let the system discover the URL automatically.');

        $settings['favicon_hide']['#title'] = $this->_model->_('Hide favicon');
        $settings['favicon_hide']['#description'] = $this->_model->_('Check this option to hide the favicon image of the feed website.');

        $settings['favicon'] = array(
            '#title' => $this->_model->_('Favicon'),
            'favicon_url' => $settings['favicon_url'],
            'favicon_hide' => $settings['favicon_hide'],
            '#collapsible' => true,
            '#collapsed' => true,
            '#weight' => 20,
        );
        unset($settings['favicon_url'], $settings['favicon_hide']);

        $settings['feed_url']['#type'] = 'url';
        $settings['feed_url']['#title'] = $this->_model->_('Feed URL');
        $settings['feed_url']['#description'] = $this->_model->_('Enter the URL of feed. Leave it blank to let the system discover the URL automatically.');
        $settings['feed_url']['#weight'] = -4;

        $settings['site_url']['#type'] = 'url';
        $settings['site_url']['#title'] = $this->_model->_('URL');
        $settings['site_url']['#description'] = $this->_model->_('Enter the URL of website providing the feed.');
        $settings['site_url']['#required'] = true;
        $settings['site_url']['#weight'] = -5;

        $settings['author_pref']['#title'] = $this->_model->_('Feed item author display preference');
        $settings['author_pref']['#options'] = array(
            Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_ENTRY_AUTHOR => $this->_model->_('Display the author name of each feed item if available. Otherwise, display the feed owner username.'),
            Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_BLOG_OWNER => $this->_model->_('Always display the feed owner username as the author.'),
        );
        $settings['author_pref']['#default_value'] = $config['authorPref'];

        $settings['allow_image']['#title'] = $this->_model->_('Allow image tags in feed items');
        $settings['allow_image']['#description'] = $this->_model->_('Check this option to enable image tags in feed items. For security reasons, it is highly recommended that you disable this feature if the feed website can not be fully trusted.');
        $settings['allow_image']['#default_value'] = $config['allowImage'];

        $settings['allow_external_resources']['#title'] = $this->_model->_('Allow external resources in feed items');
        $settings['allow_external_resources']['#description'] = $this->_model->_('Chick this option to allow resources hosted outside the feed website to be displayed in feed items, for example external website images. For security reasons, it is highly recommended that you disable this feature if the feed website can not be fully trusted.');
        $settings['allow_external_resources']['#default_value'] = $config['allowExternalResources'];

        $settings['host']['#title'] = $this->_model->_('Host');
        $settings['host']['#description'] = $this->_model->_('Enter the domain name of the feed website. Any URI that does not contain the domain name below in the host part will be considered as an external URI. Note that setting this value to example.com will include all subdomains of example.com. However, setting this value to sub.example.com will not include example.com.');
        $settings['host']['#size'] = 30;

        $settings['options'] = array('#title' => $this->_model->_('Options'), '#collapsible' => true, '#collapsed' => true, '#weight' => 30);
        foreach (array('author_pref', 'allow_image', 'allow_external_resources', 'host') as $option) {
            $settings['options'][$option] = $settings[$option];
            unset($settings[$option]);
        }

        $settings['#validate'][] = array(array($this, 'validateForm'), array($entity->id));
        
        $settings['language']['#size'] = 10;

        return $settings;
    }

    public function validateUser($form, $value, $name)
    {
        if (strlen($value) == 0) return true;

        if ($this->_model->User_IdentityByUsername($value)->isAnonymous()) {
            $form->setError($this->_model->_('User does not exist.'), $name);

            return false;
        }

        return true;
    }

    public function validateForm($form, $entityId)
    {
        $feed_r = $this->_model->Feed->criteria();

        // Try to make sure feed URL (if not provided, site URL) is unique
        if (!empty($form->values['feed_url']) && ($feed_url = rtrim($form->values['feed_url'], '/'))) {
            $feed_r = $feed_r->feedUrl_is($feed_url);
            $error_ele = 'feed_url';
        } elseif (!empty($form->values['site_url']) && ($site_url = rtrim($form->values['site_url'], '/'))) {
            $feed_r = $feed_r->siteUrl_is($site_url);
            $error_ele = 'site_url';
        } else {
            // No URL to check. Probably editing?
            return true;
        }

        if (!empty($entityId)) {
            $feed_r = $feed_r->id_isNot($entityId);
        }

        if ($feed_r->count() == 0) return true;

        $form->setError($this->_model->_('The URL is already registered.'), $error_ele);
    }
}