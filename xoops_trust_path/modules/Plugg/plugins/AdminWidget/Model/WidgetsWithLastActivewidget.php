<?php
class Plugg_AdminWidget_Model_WidgetsWithLastActivewidget extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('activewidget_last', 'Activewidget', 'activewidget_id', $collection);
    }
}