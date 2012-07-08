<?php
class Plugg_User_Model_StatusForm extends Plugg_User_Model_Base_StatusForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        unset($settings['user_id'], $settings['text_filtered'], $settings['text']['#title']);

        $settings['text']['#type'] = 'filters_textarea';
        $settings['text']['#default_value'] = array(
            'text' => $entity->text,
            'filter_id' => $entity->text_filter_id
        );

        return $settings;
    }
}