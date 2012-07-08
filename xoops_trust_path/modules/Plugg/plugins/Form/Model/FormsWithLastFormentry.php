<?php

class Plugg_Form_Model_FormsWithLastFormentry extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('formentry_last', 'Formentry', 'formentry_id', $collection);
    }
}