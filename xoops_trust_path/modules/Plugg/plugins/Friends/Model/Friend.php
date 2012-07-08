<?php
class Plugg_Friends_Model_Friend extends Plugg_Friends_Model_Base_Friend
{
    function getRelationships()
    {
        return ($relationships = $this->relationships) ? explode(' ', $relationships) : array();
    }
}

class Plugg_Friends_Model_FriendRepository extends Plugg_Friends_Model_Base_FriendRepository
{
}