<?php

class Plugg_Search_Model_SearchablesWithEngines extends Sabai_Model_EntityCollection_Decorator_AssocEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Searchable2engine', 'searchable2engine_searchable_id', 'engine', 'Engine', $collection);
    }
}