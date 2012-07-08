<?php
abstract class Plugg_Form_Model_Base_FormForm extends Plugg_PluginModelEntityForm
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
        $settings['header'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Header'),
            '#rows' => 10,
            '#default_value' => $entity->header,
            '#weight' => 4,
        );
        $settings['hidden'] = array(
            '#type' => 'checkbox',
            '#title' => $this->_model->_('Hidden'),
            '#options' => array('default' => 0),
            '#default_value' => $entity->hidden,
            '#weight' => 6,
        );
        $settings['weight'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Weight'),
            '#maxlength' => 255,
            '#default_value' => $entity->weight,
            '#weight' => 8,
        );
        $settings['submit_button_label'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Submit button label'),
            '#maxlength' => 255,
            '#default_value' => $entity->submit_button_label,
            '#weight' => 10,
        );
        $settings['confirm'] = array(
            '#type' => 'checkbox',
            '#title' => $this->_model->_('Confirm'),
            '#options' => array('default' => 0),
            '#default_value' => $entity->confirm,
            '#weight' => 12,
        );
        $settings['confirm_button_label'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Confirm button label'),
            '#maxlength' => 255,
            '#default_value' => $entity->confirm_button_label,
            '#weight' => 14,
        );

        return $settings;
    }
}