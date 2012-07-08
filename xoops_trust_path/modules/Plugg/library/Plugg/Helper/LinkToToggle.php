<?php
class Plugg_Helper_LinkToToggle extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $id, $hidden = false, $hideText = '[-]', $showText = '[+]')
    {
        $ret = array();
        $hide_toggle = $hidden ? sprintf('jQuery(document).ready(function(){jQuery("#%1$s").hide()}); jQuery.ajaxSetup({complete: function (XMLHttpRequest, textStatus){jQuery("#%1$s").hide()}});', $id) : '';
        // set display to none so that the toggle link will not show if JS disabled
        $ret[] = sprintf('<a href="" id="%s-toggle" style="display:none;">%s</a>', $id, $hidden ? $showText : $hideText);
        $ret[] = sprintf('<script type="text/javascript">
jQuery("#%1$s-toggle").css("display", "").click(function() {
  if (jQuery("#%1$s").is(":hidden")) {
    jQuery("#%1$s").slideDown("fast"); jQuery("#%1$s-toggle").text("%3$s");
  } else {
    jQuery("#%1$s").slideUp("fast"); jQuery("#%1$s-toggle").text("%2$s");
  }
  return false;
});
%4$s
</script>', $id, h($showText), h($hideText), $hide_toggle);

        return implode(PHP_EOL, $ret);
    }
}