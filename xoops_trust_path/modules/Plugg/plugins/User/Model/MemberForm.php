<?php
class Plugg_User_Model_MemberForm extends Plugg_User_Model_Base_MemberForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}