<?php
class Plugg_Helper_SelectToRemote extends Sabai_Application_Helper
{
    /**
     * Creates a selection form that when submitted updates specified portion of page using ajax
     *
     * @return string
     * @param SabaiApplication $application
     * @param string $name
     * @param string $update
     * @param array $options
     * @param array $actionUrl
     * @param string $submit
     * @param array $ajaxUrl
     * @param string $formId
     * @param bool $disableSelf
     * @param array $attributes
     * @return string
     */
    public function help(Sabai_Application $application, $name, $value, $update, array $options, $actionUrl, $submit, $ajaxUrl = array(), $formId = null, $disableSelf = false, array $attributes = array())
    {
        $form_id = !isset($formId) ? md5(uniqid(rand(), true)) : h($formId);

        if (is_array($ajaxUrl)) {
            if (is_array($actionUrl)) {
                $action_url = array_merge(array('params' => array()), $actionUrl);
                $ajax_url = array_merge($action_url, $ajaxUrl);
            } else {
                $action_url = $actionUrl;
                $ajax_url = clone $actionUrl;
                foreach (array_keys($ajaxUrl) as $k => $v) {
                    $ajax_url[$k] = $v;
                }
            }
            if (!empty($ajaxUrl['params'])) {
                $ajax_url['params'] = array_merge($action_url['params'], $ajaxUrl['params'], array(Plugg::PARAM_AJAX => $update));
            } else {
                $ajax_url['params'] = array_merge($action_url['params'], array(Plugg::PARAM_AJAX => $update));
            }
        } else {
            $ajax_url = $ajaxUrl;
        }
        $ajax_url['separator'] = '&';

        $html[] = sprintf(
            '<form id="%3$s" style="display:inline; margin:0; padding:0;" method="get" action="%2$s"><select name="%1$s">',
            h($name), is_array($action_url) ? $application->createUrl($action_url) : $action_url, $form_id
        );
        foreach (array_keys($options) as $v) {
            if ($v == $value) {
                $html[] = sprintf('<option value="%s" selected="selected">%s</option>', h($v), h($options[$v]));
            } else {
                $html[] = sprintf('<option value="%s">%s</option>', h($v), h($options[$v]));
            }
        }
        $html[] = sprintf('</select> <input id="%s-submit" type="submit" value="%s" />', $form_id, h($submit));
        foreach ($action_url['params'] as $param_k => $param_v) {
            $html[] = sprintf('<input type="hidden" name="%s" value="%s" />', h($param_k), h($param_v));
        }
        $action_url['base'] = !isset($action_url['base']) ? $application->getUrlBase() : $action_url['base'];

        $html[] = sprintf('<input type="hidden" name="%3$s" value="%4$s" />
</form>
<script type="text/javascript">
jQuery("#%5$s > input").css("display", "none");
jQuery("#%5$s > select").change(function() {
  jQuery.ajax({
    url: "%2$s",
    type: "get",
    dataType: "html",
    data: jQuery("#%5$s > select").serialize(),
    beforeSend: function(req) {
      jQuery.scrollTo(jQuery("#%1$s"), 1000, {offset: 50});
    },
    success: function(data) {
      jQuery("#%1$s").html(data);
    }
  });
});
</script>',
            $update,
            is_array($ajax_url) ? $application->createUrl($ajax_url) : $ajax_url,
            $application->getRouteParam(),
            $action_url['base'] . @$action_url['path'],
            $form_id
        );

        return implode(PHP_EOL, $html);
    }
}