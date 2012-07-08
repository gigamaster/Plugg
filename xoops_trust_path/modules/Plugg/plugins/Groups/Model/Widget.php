<?php
class Plugg_Groups_Model_Widget extends Plugg_Groups_Model_Base_Widget
{
    public function isCacheable()
    {
        return $this->type & Plugg_Groups_Plugin::WIDGET_TYPE_CACHEABLE;
    }
}

class Plugg_Groups_Model_WidgetRepository extends Plugg_Groups_Model_Base_WidgetRepository
{
}