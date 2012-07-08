<?php
class Plugg_Search_Model_SearchableForm extends Plugg_Search_Model_Base_SearchableForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}