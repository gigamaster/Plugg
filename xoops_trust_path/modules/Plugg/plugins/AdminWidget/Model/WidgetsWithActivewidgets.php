<?php
class Plugg_AdminWidget_Model_WidgetsWithActivewidgets extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('activewidget_widget_id', 'Activewidget', $collection);
    }
}