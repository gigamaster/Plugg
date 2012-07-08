<?php
abstract class Plugg_Search_Model_Base_Searchable2engineForm extends Plugg_PluginModelEntityForm
{
    public function __construct(Plugg_PluginModel $model)
    {
        parent::__construct($model);
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = array();
        $settings['Searchable'] = array(
            '#type' => 'selectmodelentity',
            '#title' => $this->_model->_('searchable'),
            '#model' => $this->_model,
            '#entity' => 'Searchable',
            '#default_value' => $entity->searchable_id,
            '#weight' => 2,
        );
        $settings['Engine'] = array(
            '#type' => 'selectmodelentity',
            '#title' => $this->_model->_('engine'),
            '#model' => $this->_model,
            '#entity' => 'Engine',
            '#default_value' => $entity->engine_id,
            '#weight' => 4,
        );

        return $settings;
    }
}