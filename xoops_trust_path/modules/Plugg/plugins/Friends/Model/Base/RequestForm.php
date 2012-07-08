<?php
abstract class Plugg_Friends_Model_Base_RequestForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['message'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Message'),
            '#rows' => 10,
            '#default_value' => $entity->message,
            '#weight' => 2,
        );
        $settings['user_id'] = array(
            '#title' => $this->_model->_('User ID'),
            '#maxlength' => 10,
            '#default_value' => $entity->user_id,
            '#weight' => 4,
        );

        return $settings;
    }
}