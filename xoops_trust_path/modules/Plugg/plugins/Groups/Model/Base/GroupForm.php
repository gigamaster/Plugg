<?php
abstract class Plugg_Groups_Model_Base_GroupForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Name'),
            '#maxlength' => 255,
            '#default_value' => $entity->name,
            '#weight' => 2,
        );
        $settings['display_name'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Display name'),
            '#maxlength' => 255,
            '#required' => true,
            '#default_value' => $entity->display_name,
            '#weight' => 4,
        );
        $settings['description'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Description'),
            '#rows' => 10,
            '#required' => true,
            '#default_value' => $entity->description,
            '#weight' => 6,
        );
        $settings['type'] = array(
            '#type' => 'radios',
            '#title' => $this->_model->_('Type'),
            '#options' => array(),
            '#required' => true,
            '#default_value' => $entity->type,
            '#weight' => 8,
        );
        $settings['user_id'] = array(
            '#title' => $this->_model->_('User ID'),
            '#maxlength' => 10,
            '#default_value' => $entity->user_id,
            '#weight' => 10,
        );

        return $settings;
    }
}