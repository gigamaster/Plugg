<?php
class Plugg_Forum_Model_StarForm extends Plugg_Forum_Model_Base_StarForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}