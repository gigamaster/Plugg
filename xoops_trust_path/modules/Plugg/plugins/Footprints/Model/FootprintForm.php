<?php
class Plugg_Footprints_Model_FootprintForm extends Plugg_Footprints_Model_Base_FootprintForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}