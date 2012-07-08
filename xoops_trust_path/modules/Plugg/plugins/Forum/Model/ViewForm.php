<?php
class Plugg_Forum_Model_ViewForm extends Plugg_Forum_Model_Base_ViewForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}