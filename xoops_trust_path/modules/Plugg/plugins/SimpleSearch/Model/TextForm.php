<?php
class Plugg_SimpleSearch_Model_TextForm extends Plugg_SimpleSearch_Model_Base_TextForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}