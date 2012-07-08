<?php
class Plugg_System_Model_RoutesWithParentsCount extends Sabai_Model_EntityCollection_Decorator_ParentEntitiesCount
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Route', $collection);
    }
}