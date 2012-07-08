<?php
abstract class Plugg_Footprints_Model_Base_FootprintForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['target'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Target'),
            '#maxlength' => 255,
            '#default_value' => $entity->target,
            '#weight' => 2,
        );
        $settings['hidden'] = array(
            '#type' => 'yesno',
            '#title' => $this->_model->_('Hidden'),
            '#delimiter' => '&nbsp;',
            '#default_value' => $entity->hidden,
            '#weight' => 4,
        );
        $settings['user_id'] = array(
            '#title' => $this->_model->_('User ID'),
            '#maxlength' => 10,
            '#default_value' => $entity->user_id,
            '#weight' => 6,
        );

        return $settings;
    }
}