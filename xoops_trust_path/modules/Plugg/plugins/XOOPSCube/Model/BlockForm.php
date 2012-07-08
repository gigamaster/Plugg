<?php
class Plugg_XOOPSCube_Model_BlockForm extends Plugg_XOOPSCube_Model_Base_BlockForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}