<?php
class Plugg_System_Model_AdminroutesWithChildren extends Sabai_Model_EntityCollection_Decorator_ChildEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Adminroute', 'adminroute_parent', $collection);
    }
}