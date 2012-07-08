<?php
class Plugg_Filters_Model_FilterForm extends Plugg_Filters_Model_Base_FilterForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}