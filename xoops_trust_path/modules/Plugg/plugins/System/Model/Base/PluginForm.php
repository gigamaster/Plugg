<?php
abstract class Plugg_System_Model_Base_PluginForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['nicename'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Nicename'),
            '#maxlength' => 255,
            '#required' => true,
            '#default_value' => $entity->nicename,
            '#weight' => 2,
        );
        $settings['active'] = array(
            '#type' => 'yesno',
            '#title' => $this->_model->_('Active'),
            '#delimiter' => '&nbsp;',
            '#default_value' => $entity->active,
            '#weight' => 4,
        );
        $settings['locked'] = array(
            '#type' => 'yesno',
            '#title' => $this->_model->_('Locked'),
            '#delimiter' => '&nbsp;',
            '#default_value' => $entity->locked,
            '#weight' => 6,
        );

        return $settings;
    }
}