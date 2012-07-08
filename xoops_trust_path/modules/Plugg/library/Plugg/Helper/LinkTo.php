<?php
class Plugg_Helper_LinkTo extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $linkText, $urlParts, array $attributes = null)
    {
        if (!empty($attributes)) {
            $_attributes = array();
            foreach ($attributes as $k => $v) {
                $_attributes[] = sprintf(' %s="%s"', $k, h($v, ENT_COMPAT));
            }
            $attr = implode('', $_attributes);
        } else {
            $attr = '';
        }
        $url = is_array($urlParts) ? $application->createUrl($urlParts) : $urlParts;

        return sprintf('<a href="%s"%s>%s</a>', $url, $attr, h($linkText));
    }

}