<?php

class Plugg_Search_Model_SearchablesWithEngineCount extends Sabai_Model_EntityCollection_Decorator_AssocEntitiesCount
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Searchable2engine', 'searchable2engine_searchable_id', 'Engine', $collection);
    }
}