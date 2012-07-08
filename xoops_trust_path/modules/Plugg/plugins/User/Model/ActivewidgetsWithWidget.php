<?php
class Plugg_User_Model_ActivewidgetsWithWidget extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('widget_id', 'Widget', 'widget_id', $collection);
    }
}