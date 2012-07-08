<?php
abstract class Plugg_Aggregator_Model_Base_ItemForm extends Plugg_PluginModelEntityForm
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
        $settings['url'] = array(
            '#type' => 'url',
            '#title' => $this->_model->_('Url'),
            '#maxlength' => 255,
            '#default_value' => $entity->url,
            '#weight' => 4,
        );
        $settings['body'] = array(
            '#type' => 'textarea',
            '#title' => $this->_model->_('Body'),
            '#rows' => 10,
            '#default_value' => $entity->body,
            '#weight' => 6,
        );
        $settings['author'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Author'),
            '#maxlength' => 255,
            '#default_value' => $entity->author,
            '#weight' => 8,
        );
        $settings['author_link'] = array(
            '#type' => 'textfield',
            '#title' => $this->_model->_('Author link'),
            '#maxlength' => 255,
            '#default_value' => $entity->author_link,
            '#weight' => 10,
        );
        $settings['hidden'] = array(
            '#type' => 'yesno',
            '#title' => $this->_model->_('Hidden'),
            '#delimiter' => '&nbsp;',
            '#default_value' => $entity->hidden,
            '#weight' => 12,
        );

        return $settings;
    }
}