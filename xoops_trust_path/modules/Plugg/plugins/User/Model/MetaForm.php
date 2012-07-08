<?php
class Plugg_User_Model_MetaForm extends Plugg_User_Model_Base_MetaForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}