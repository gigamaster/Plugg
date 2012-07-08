<?php

class Plugg_System_Model_PluginsWithLastDependency extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('dependency_last', 'Dependency', 'dependency_id', $collection);
    }
}