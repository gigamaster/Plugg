<?php
class Plugg_Widgets_Model_WidgetForm extends Plugg_Widgets_Model_Base_WidgetForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}