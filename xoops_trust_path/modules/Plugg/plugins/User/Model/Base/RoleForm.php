<?php
abstract class Plugg_User_Model_Base_RoleForm extends Plugg_PluginModelEntityForm
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

        return $settings;
    }
}