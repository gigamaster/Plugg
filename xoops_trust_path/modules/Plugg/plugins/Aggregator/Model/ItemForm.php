<?php
class Plugg_Aggregator_Model_ItemForm extends Plugg_Aggregator_Model_Base_ItemForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}