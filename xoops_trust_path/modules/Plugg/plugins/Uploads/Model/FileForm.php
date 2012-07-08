<?php
class Plugg_Uploads_Model_FileForm extends Plugg_Uploads_Model_Base_FileForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}