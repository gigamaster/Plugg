<?php
abstract class Plugg_Groups_Model_Base_MemberForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['role'] = array(
            '#type' => 'radios',
            '#title' => $this->_model->_('Role'),
            '#options' => array(),
            '#required' => true,
            '#default_value' => $entity->role,
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