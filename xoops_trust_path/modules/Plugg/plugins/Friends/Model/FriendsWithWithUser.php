<?php
class Plugg_Friends_Model_FriendsWithWithUser extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, false, 'with', 'WithUser');
    }
}