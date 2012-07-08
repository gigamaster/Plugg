<?php
class Plugg_Search_Model_EngineForm extends Plugg_Search_Model_Base_EngineForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}