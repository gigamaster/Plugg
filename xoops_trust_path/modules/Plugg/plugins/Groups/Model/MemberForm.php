<?php
class Plugg_Groups_Model_MemberForm extends Plugg_Groups_Model_Base_MemberForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}