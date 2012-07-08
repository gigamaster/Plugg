<?php
class Plugg_User_Model_FieldsWithFormField extends Plugg_Form_Model_WithField
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, 'field_id', 'FormField');
    }
}