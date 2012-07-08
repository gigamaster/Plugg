<?php

class Plugg_Search_Model_EnginesWithSearchableCount extends Sabai_Model_EntityCollection_Decorator_AssocEntitiesCount
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Searchable2engine', 'searchable2engine_engine_id', 'Searchable', $collection);
    }
}