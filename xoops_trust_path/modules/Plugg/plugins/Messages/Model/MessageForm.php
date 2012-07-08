<?php
class Plugg_Messages_Model_MessageForm extends Plugg_Messages_Model_Base_MessageForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        unset($settings['user_id'], $settings['body_html']);

        $settings['body']['#type'] = 'filters_textarea'; // make it filterable

        return $settings;
    }
}