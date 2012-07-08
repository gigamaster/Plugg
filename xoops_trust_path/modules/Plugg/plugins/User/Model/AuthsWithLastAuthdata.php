<?php
class Plugg_User_Model_AuthsWithLastAuthdata extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('authdata_last', 'Authdata', 'authdata_id', $collection);
    }
}