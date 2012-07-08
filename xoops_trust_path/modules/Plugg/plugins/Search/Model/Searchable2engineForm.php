<?php
class Plugg_Search_Model_Searchable2engineForm extends Plugg_Search_Model_Base_Searchable2engineForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}