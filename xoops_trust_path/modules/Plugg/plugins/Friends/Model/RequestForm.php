<?php
class Plugg_Friends_Model_RequestForm extends Plugg_Friends_Model_Base_RequestForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        unset($settings['user_id']);
        
        $settings['message']['#rows'] = 5;
        
        return $settings;
    }
}