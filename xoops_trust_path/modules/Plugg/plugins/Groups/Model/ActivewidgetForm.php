<?php
class Plugg_Groups_Model_ActivewidgetForm extends Plugg_Groups_Model_Base_ActivewidgetForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}