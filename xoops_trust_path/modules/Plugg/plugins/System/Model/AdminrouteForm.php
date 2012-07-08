<?php
class Plugg_System_Model_AdminrouteForm extends Plugg_System_Model_Base_AdminrouteForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}