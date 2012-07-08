<?php
class Plugg_System_Model_DependencyForm extends Plugg_System_Model_Base_DependencyForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}