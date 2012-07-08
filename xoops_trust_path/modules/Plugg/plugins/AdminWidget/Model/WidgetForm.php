<?php
class Plugg_AdminWidget_Model_WidgetForm extends Plugg_AdminWidget_Model_Base_WidgetForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}