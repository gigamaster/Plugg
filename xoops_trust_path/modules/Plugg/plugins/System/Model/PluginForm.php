<?php
class Plugg_System_Model_PluginForm extends Plugg_System_Model_Base_PluginForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}