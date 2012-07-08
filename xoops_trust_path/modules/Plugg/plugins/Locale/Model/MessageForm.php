<?php
class Plugg_Locale_Model_MessageForm extends Plugg_Locale_Model_Base_MessageForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}