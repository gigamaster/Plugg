<?php
class Plugg_User_Model_QueueForm extends Plugg_User_Model_Base_QueueForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}