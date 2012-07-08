<?php

class Plugg_System_Model_PluginsWithDependencies extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('dependency_plugin_id', 'Dependency', $collection);
    }
}