<?php
class Plugg_Helper_ImportRemote extends Sabai_Application_Helper
{
    /**
     * Imports content using ajax to a specific part of the page
     *
     * @param Sabai_Application $application
     * @param string $id
     * @param array $remoteUrl
     */
    public function help(Sabai_Application $application, $id, $remoteUrl = array())
    {
        $remoteUrl['params'] = array_merge((array)@$remoteUrl['params'], array(
            Plugg::PARAM_AJAX => $id,
        ));
        $remoteUrl['separator'] = '&';
        $url = is_array($remoteUrl) ? $application->createUrl($remoteUrl) : $remoteUrl;

        printf('<script type="text/javascript">
jQuery(document).ready(function() {
jQuery.ajax({
  success: function(data) {
    jQuery("#%1$s").html(data);
  },
  error: function(request, status, error) {
    jQuery("#%1$s").text(error);
  },
  type: "get",
  dataType: "html",
  url: "%2$s"
});
});
</script>', $id, $url);
    }
}