<?php
class Plugg_Footprints_Model_FootprintsWithTargetUser extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, false, 'target', 'TargetUser');
    }
}