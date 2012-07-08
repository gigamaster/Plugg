<?php
class Plugg_System_Model_RoutesWithChildren extends Sabai_Model_EntityCollection_Decorator_ChildEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Route', 'route_parent', $collection);
    }
}