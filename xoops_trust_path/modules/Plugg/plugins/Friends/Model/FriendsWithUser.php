<?php
class Plugg_Friends_Model_FriendsWithUser extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}