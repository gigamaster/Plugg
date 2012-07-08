<?php
class Plugg_Widgets_Controller_Main_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent(
            $this->RenderTemplate('widgets_main_index', array('widgets' => $this->_getWidgetContents($request)))
        );
    }

    private function _getWidgetContents(Sabai_Request $request)
    {
        $ret = array(
            Plugg_Widgets_Plugin::WIDGET_POSITION_TOP => array(),
            Plugg_Widgets_Plugin::WIDGET_POSITION_BOTTOM => array(),
            Plugg_Widgets_Plugin::WIDGET_POSITION_LEFT => array(),
            Plugg_Widgets_Plugin::WIDGET_POSITION_RIGHT => array(),
        );

        $need_commit = false;
        foreach ($this->_getWidgets($request)->with('Widget') as $widget) {

            if (!$widget->Widget
                || !$widget->Widget->canViewContent($this->getUser()) // the user may not view content of this widget
                || (!$plugin = $this->getPlugin($widget->Widget->plugin)) // not a valid plugin widget
            ) continue;

            if (!$widget->Widget->isCacheable() // content of this widget may not be cached
                || !$widget->cache_lifetime
                || !$widget->cache // no cache
                || $widget->cache_time + $widget->cache_lifetime < time() // cache expired
                || (false === $widget_content = unserialize($widget->cache)) // failed unserializing cached content
            ) {
                // Build widget content
                if (false === $widget_content = $this->_buildWidgetContent($request, $widget, $plugin)) {
                    continue;
                }

                if ($widget->cache_lifetime) {
                    // Cache content
                    $widget->cache = serialize($widget_content);
                    $widget->cache_time = time();

                    $need_commit = true;
                }
            }

            $ret[$widget->position][] = array(
                'id' => $widget->id,
                'title' => $widget->title,
                'content' => isset($widget_content['content']) ? $widget_content['content'] : array(),
                'menu' => (array)@$widget_content['menu'],
                'links' => (array)@$widget_content['links'],
                'name' => $widget->Widget->name,
                'plugin' => $plugin->name,
                'path' => $plugin->name . '/' . $widget->Widget->name,
            );
        }

        // Commit widget caches
        if ($need_commit) $this->getPluginModel()->commit();

        return $ret;
    }

    private function _getWidgets($request)
    {
        return $this->getPluginModel()->Activewidget->fetch(0, 0, 'order', 'ASC');
    }

    private function _buildWidgetContent($request, $widget, $plugin)
    {
        // Render widget content
        return $plugin->widgetsGetWidgetContent(
            $widget->Widget->name,
            unserialize($widget->settings),
            $this->getUser()
        );
    }
}