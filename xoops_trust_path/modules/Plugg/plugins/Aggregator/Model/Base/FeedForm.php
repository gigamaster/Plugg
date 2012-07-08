<?php
abstract class Plugg_Aggregator_Model_Base_FeedForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['site_url'] = array(
            '#type' => 'url',
            '#title' => $this->_model->_('Site url'),
            '#maxlength' => 255,
            '#default_value' => $entity->site_url,
            '#weight' => 2,
        );
        $settings['title'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Title'),
            '#maxlength' => 255,
            '#default_value' => $entity->title,
            '#weight' => 4,
        );
        $settings['description'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Description'),
            '#rows' => 5,
            '#default_value' => $entity->description,
            '#weight' => 6,
        );
        $settings['language'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Language'),
            '#maxlength' => 255,
            '#default_value' => $entity->language,
            '#weight' => 8,
        );
        $settings['feed_url'] = array(
            '#type' => 'url',
            '#title' => $this->_model->_('Feed url'),
            '#maxlength' => 255,
            '#default_value' => $entity->feed_url,
            '#weight' => 10,
        );
        $settings['favicon_url'] = array(
            '#type' => 'url',
            '#title' => $this->_model->_('Favicon url'),
            '#maxlength' => 255,
            '#default_value' => $entity->favicon_url,
            '#weight' => 12,
        );
        $settings['favicon_hide'] = array(
            '#type' => 'checkbox',
            '#title' => $this->_model->_('Favicon hide'),
            '#options' => array('default' => 0),
            '#default_value' => $entity->favicon_hide,
            '#weight' => 14,
        );
        $settings['author_pref'] = array(
            '#type' => 'radios',
            '#title' => $this->_model->_('Author pref'),
            '#options' => array(),
            '#default_value' => $entity->author_pref,
            '#weight' => 16,
        );
        $settings['allow_image'] = array(
            '#type' => 'checkbox',
            '#title' => $this->_model->_('Allow image'),
            '#options' => array('default' => 0),
            '#default_value' => $entity->allow_image,
            '#weight' => 18,
        );
        $settings['allow_external_resources'] = array(
            '#type' => 'checkbox',
            '#title' => $this->_model->_('Allow external resources'),
            '#options' => array('default' => 0),
            '#default_value' => $entity->allow_external_resources,
            '#weight' => 20,
        );
        $settings['host'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Host'),
            '#maxlength' => 255,
            '#default_value' => $entity->host,
            '#weight' => 22,
        );
        $settings['user_id'] = array(
            '#title' => $this->_model->_('User ID'),
            '#maxlength' => 10,
            '#default_value' => $entity->user_id,
            '#weight' => 24,
        );

        return $settings;
    }
}