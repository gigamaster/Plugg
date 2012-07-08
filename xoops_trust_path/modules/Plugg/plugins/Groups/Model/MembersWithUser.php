<?php
class Plugg_Groups_Model_MembersWithUser extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}