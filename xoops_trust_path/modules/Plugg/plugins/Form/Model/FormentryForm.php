<?php
class Plugg_Form_Model_FormentryForm extends Plugg_Form_Model_Base_FormentryForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}