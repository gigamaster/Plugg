<?php

class Plugg_Form_Model_FieldsWithLastFormfield extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('formfield_last', 'Formfield', 'formfield_id', $collection);
    }
}