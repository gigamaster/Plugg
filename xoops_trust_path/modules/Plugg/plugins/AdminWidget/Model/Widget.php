<?php
class Plugg_AdminWidget_Model_Widget extends Plugg_AdminWidget_Model_Base_Widget
{
    public function isCacheable()
    {
        return $this->type & Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE;
    }
}

class Plugg_AdminWidget_Model_WidgetRepository extends Plugg_AdminWidget_Model_Base_WidgetRepository
{
}