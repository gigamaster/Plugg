<?php
abstract class Plugg_Messages_Model_Base_MessageForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['title'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Title'),
            '#maxlength' => 255,
            '#required' => true,
            '#default_value' => $entity->title,
            '#weight' => 2,
        );
        $settings['body'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Body'),
            '#rows' => 10,
            '#required' => true,
            '#default_value' => $entity->body,
            '#weight' => 4,
        );
        $settings['body_html'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Body html'),
            '#rows' => 10,
            '#default_value' => $entity->body_html,
            '#weight' => 6,
        );
        $settings['user_id'] = array(
            '#title' => $this->_model->_('User ID'),
            '#maxlength' => 10,
            '#default_value' => $entity->user_id,
            '#weight' => 8,
        );

        return $settings;
    }
}