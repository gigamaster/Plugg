<?php
class Plugg_Helper_FormTag extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $method = 'post', $actionUrl = array(), array $attributes = array())
    {
        $route_html = '';
        if (strcasecmp($method, 'get') == 0) {
            $method = 'get';
            // embed route parameter if method is get and route is not an empty string
            if (!empty($actionUrl['base']) || !empty($actionUrl['path'])) {
                $route_html = sprintf(
                    '<input type="hidden" name="%s" value="%s%s" />',
                    $application->getRouteParam(), @$actionUrl['base'], @$actionUrl['path']
                );
            }
        } else {
            $method = 'post';
        }
        if (!empty($actionUrl)) {
            $attributes['action'] = is_array($actionUrl) ? $application->createUrl($actionUrl) : $actionUrl;
        }
        $attr = array();
        foreach ($attributes as $k => $v) {
            $attr[] = sprintf(' %s="%s"', $k, h($v, ENT_COMPAT));
        }

        printf('<form method="%s"%s>%s', $method, implode('', $attr), $route_html);
    }
}