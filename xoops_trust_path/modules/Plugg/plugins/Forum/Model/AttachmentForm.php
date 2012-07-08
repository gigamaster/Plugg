<?php
class Plugg_Forum_Model_AttachmentForm extends Plugg_Forum_Model_Base_AttachmentForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        return $settings;
    }
}