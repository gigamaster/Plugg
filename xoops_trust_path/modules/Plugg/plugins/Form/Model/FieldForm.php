<?php
class Plugg_Form_Model_FieldForm extends Plugg_Form_Model_Base_FieldForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}