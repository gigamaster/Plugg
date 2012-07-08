<?php
abstract class Plugg_Search_Model_Base_SearchableForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['Engines'] = array(
            '#type' => 'selectmodelentity',
            '#title' => $this->_model->_('Engine'),
            '#model' => $this->_model,
            '#entity' => 'Engine',
            '#default_value' => $entity->Engines->getAllIds(),
            '#weight' => 2,
        );

        return $settings;
    }
}