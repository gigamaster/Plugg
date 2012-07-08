<?php
class Plugg_User_Model_AuthdataForm extends Plugg_User_Model_Base_AuthdataForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}