<?php
abstract class Plugg_Forum_Model_Base_CommentForm extends Plugg_PluginModelEntityForm
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
        $settings['Parent'] = array(
            '#type' => 'selectmodeltreeentity',
            '#title' => $this->_model->_('Parent comment'),
            '#model' => $this->_model,
            '#entity' => 'Comment',
            '#prefix' => ' - ',
            '#default_value' => $entity->parent,
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