<?php
class Plugg_HTMLPurifierFilter_Model_CustomfilterForm extends Plugg_HTMLPurifierFilter_Model_Base_CustomfilterForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}