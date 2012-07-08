<?php

class Plugg_Search_Model_EnginesWithSearchables extends Sabai_Model_EntityCollection_Decorator_AssocEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Searchable2engine', 'searchable2engine_engine_id', 'searchable', 'Searchable', $collection);
    }
}