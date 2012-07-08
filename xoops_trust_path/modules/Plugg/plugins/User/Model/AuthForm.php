<?php
class Plugg_User_Model_AuthForm extends Plugg_User_Model_Base_AuthForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}