<?php
abstract class Plugg_User_Model_Base_StatusForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['text'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Text'),
            '#rows' => 10,
            '#default_value' => $entity->text,
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