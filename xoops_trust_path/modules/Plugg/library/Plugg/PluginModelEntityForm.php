<?php
abstract class Plugg_PluginModelEntityForm
{
    protected $_model;

    protected function __construct(Plugg_PluginModel $model)
    {
        $this->_model = $model;
    }

    abstract public function getSettings(Sabai_Model_Entity $entity);
}