<?php
class Plugg_Helper_LinkToRemote extends Sabai_Application_Helper
{
    function help(Sabai_Application $application, $linkText, $update, $linkUrl, $ajaxUrl = array(), array $options = array(), array $attributes = null)
    {
        $html = $ajax_options = array();
        $replace = '';

        if (empty($options['no_escape'])) $linkText = h($linkText);

        if (!empty($options['toggle'])) {
            $my_id = md5(uniqid(rand(), true));
            $attributes['id'] = $my_id . '-show';
            $ajax_options[] = sprintf("effect:'slide', onContent:function(xhr){jQuery('#%1\$s-show').hide();jQuery('#%1\$s-hide').show();}", $my_id);
            $toggle_onclick = sprintf("if(jQuery('#%1\$s').is(':hidden')){jQuery('#%1\$s').slideDown('fast');}else{jQuery('#%1\$s').slideUp('fast');}return false;", $update);
            $html[] = sprintf('<a href="" id="%1$s-hide" style="display:none;" onclick="%3$s" class="%4$s toggleProcessed">%2$s</a>', $my_id, $linkText, $toggle_onclick, @$attributes['class']);
            $attributes['class'] = !empty($attributes['class']) ? $attributes['class'] . ' toggle' : 'toggle';
        } elseif (!empty($options['slide'])) {
            $ajax_options[] = "effect:'slide'";
        }

        // Add optional effects
        if (!empty($options['scroll'])) $ajax_options[] = 'scrollTo:true';
        if (!empty($options['highlight'])) $ajax_options[] = 'highlight:true';
        if (!empty($options['updateParent'])) $ajax_options[] = 'updateParent:true';

        // Set success/error/content handlers
        if (!empty($options['success'])) {
            $ajax_options[] = 'onSuccess:function(xhr, result, target){' . $options['success'] . '}';
        }
        if (!empty($options['successUrl'])) {
            if (isset($options['successUrl']['params'])) {
                $options['successUrl']['params'] = array_merge($options['successUrl']['params'], array(Plugg::PARAM_AJAX => $update));
            } else {
                $options['successUrl']['params'] = array(Plugg::PARAM_AJAX => $update);
            }
            $ajax_options[] = sprintf(
                "onSuccessUrl:'%s'",
                is_array($options['successUrl']) ? $application->createUrl($options['successUrl']) : $options['successUrl']
            );
        } elseif (isset($options['successRedirect']) && !$options['successRedirect']) {
            $ajax_options[] = 'onSuccessRedirect:false';
        }
        if (!empty($options['error'])) {
            $ajax_options[] = 'onError:function(xhr, result, target){' . $options['error'] . '}';
        }
        if (!empty($options['errorUrl'])) {
            if (isset($options['errorUrl']['params'])) {
                $options['errorUrl']['params'] = array_merge($options['errorUrl']['params'], array(Plugg::PARAM_AJAX => $update));
            } else {
                $options['errorUrl']['params'] = array(Plugg::PARAM_AJAX => $update);
            }
            $ajax_options[] = sprintf(
                "onErrorUrl:'%s'",
                is_array($options['errorUrl']) ? $application->createUrl($options['errorUrl']) : $options['errorUrl']
            );
        } elseif (isset($options['errorRedirect']) && !$options['errorRedirect']) {
            $ajax_options[] = 'onErrorRedirect:false';
        }
        if (!empty($options['content'])) {
            $ajax_options[] = 'onContent:function(xhr, target){' . $options['content'] . '}';
        }

        if (is_array($ajaxUrl)) {
            if (is_array($linkUrl)) {
                $linkUrl = array_merge(array('params' => array()), $linkUrl);
                $ajax_url = array_merge($linkUrl, $ajaxUrl);
            } else {
                $ajax_url = clone $linkUrl;
                foreach (array_keys($ajaxUrl) as $k => $v) {
                    $ajax_url[$k] = $v;
                }
            }
            if (!empty($ajaxUrl['params'])) {
                $ajax_url['params'] = array_merge($linkUrl['params'], $ajaxUrl['params'], array(Plugg::PARAM_AJAX => $update));
            } else {
                $ajax_url['params'] = array_merge($linkUrl['params'], array(Plugg::PARAM_AJAX => $update));
            }
        } else {
            $ajax_url = $ajaxUrl;
            $ajax_url['params'] = array_merge($ajax_url['params'], array(Plugg::PARAM_AJAX => $update));
        }
        $ajax_url['separator'] = '&';

        if (!empty($options['post'])) {
            $ajax_options[] = "type:'post'";
            $ajax_params = array();
            foreach ($ajax_url['params'] as $param_k => $param_v) {
                $ajax_params[] = sprintf("%s:'%s'", $param_k, h($param_v));
            }
            $ajax_options[] = sprintf('data:{%s}', implode(',', $ajax_params));
            $ajax_url['params'] = array();
        } else {
            $ajax_options[] = "type:'get'";
        }

        $ajax_options[] = sprintf(
            "target:'#%s', dataType:'html', url:'%s'",
            $update,
            is_array($ajax_url) ? $application->createUrl($ajax_url) : $ajax_url
        );

        if (!empty($options['replace'])) {
            $replace = sprintf("jQuery(this).parent().html('%s');", $options['replace']);
        }

        $attributes['onclick'] = sprintf('jQuery.plugg.ajax({%1$s}); %2$s %3$s return false;', implode(',', $ajax_options), $replace, @$options['other']);
        $html[] = $this->_getLink($application, $linkText, $linkUrl, $attributes);

        return implode(PHP_EOL, $html);
    }

    private function _getLink($application, $linkText, $linkUrl, $attributes)
    {
        if (!empty($attributes)) {
            $_attributes = array();
            foreach ($attributes as $k => $v) {
                $_attributes[] = sprintf(' %s="%s"', $k, h($v, ENT_COMPAT)); // Avoid escaping quotes used in javascript
            }
            $attr = implode('', $_attributes);
        } else {
            $attr = '';
        }

        return sprintf(
            '<a href="%s"%s>%s</a>',
            is_array($linkUrl) ? $application->createUrl($linkUrl) : $linkUrl,
            $attr,
            $linkText
        );
    }
}