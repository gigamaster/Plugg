<?php

class Plugg_System_Model_DependenciesWithPlugin extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('plugin_id', 'Plugin', 'plugin_id', $collection);
    }
}