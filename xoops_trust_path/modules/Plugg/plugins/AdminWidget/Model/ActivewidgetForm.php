<?php
class Plugg_AdminWidget_Model_ActivewidgetForm extends Plugg_AdminWidget_Model_Base_ActivewidgetForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}