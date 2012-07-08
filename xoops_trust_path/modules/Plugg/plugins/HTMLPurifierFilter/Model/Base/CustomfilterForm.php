<?php
abstract class Plugg_HTMLPurifierFilter_Model_Base_CustomfilterForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();

        return $settings;
    }
}