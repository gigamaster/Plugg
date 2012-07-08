<?php
class Plugg_User_Model_Field extends Plugg_User_Model_Base_Field
{
    function isType($type)
    {
        return ($this->type & $type) == $type;
    }
}

class Plugg_User_Model_FieldRepository extends Plugg_User_Model_Base_FieldRepository
{
}