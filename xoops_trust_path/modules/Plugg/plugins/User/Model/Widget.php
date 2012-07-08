<?php
class Plugg_User_Model_Widget extends Plugg_User_Model_Base_Widget
{
    function isType($type)
    {
        return ($this->type & $type) == $type;
    }

    public function isCacheable()
    {
        return $this->type & Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE;
    }
}

class Plugg_User_Model_WidgetRepository extends Plugg_User_Model_Base_WidgetRepository
{
}