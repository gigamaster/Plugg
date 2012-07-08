<?php
abstract class Plugg_Search_Model_Base_EngineForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['Searchables'] = array(
            '#type' => 'selectmodelentity',
            '#title' => $this->_model->_('Searchable'),
            '#model' => $this->_model,
            '#entity' => 'Searchable',
            '#default_value' => $entity->Searchables->getAllIds(),
            '#weight' => 2,
        );

        return $settings;
    }
}