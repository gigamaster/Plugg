<?php
class Plugg_Friends_Model_FriendForm extends Plugg_Friends_Model_Base_FriendForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}