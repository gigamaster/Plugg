<?php
class Plugg_Widgets_Model_Widget extends Plugg_Widgets_Model_Base_Widget
{
    public function isCacheable()
    {
        return $this->type & Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE;
    }

    public function canViewContent(Sabai_User $user)
    {
        if ($this->type & Plugg_Widgets_Plugin::WIDGET_TYPE_REQUIRE_AUTHENTICATED) {
            return $user->isAuthenticated();
        } elseif ($this->type & Plugg_Widgets_Plugin::WIDGET_TYPE_REQUIRE_ANONYMOUS) {
            return !$user->isAuthenticated();
        }

        return true;
    }
}

class Plugg_Widgets_Model_WidgetRepository extends Plugg_Widgets_Model_Base_WidgetRepository
{
}