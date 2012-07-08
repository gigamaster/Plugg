<?php
class Plugg_XOOPSCube_Model_SearchForm extends Plugg_XOOPSCube_Model_Base_SearchForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}