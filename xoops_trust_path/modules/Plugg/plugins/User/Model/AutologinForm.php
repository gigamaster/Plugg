<?php
class Plugg_User_Model_AutologinForm extends Plugg_User_Model_Base_AutologinForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}