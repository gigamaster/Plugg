<?php
class Plugg_System_Model_RouteForm extends Plugg_System_Model_Base_RouteForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}