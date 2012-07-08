<?php
class Plugg_User_Model_StatForm extends Plugg_User_Model_Base_StatForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}