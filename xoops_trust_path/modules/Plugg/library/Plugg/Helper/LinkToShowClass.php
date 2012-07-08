<?php
class Plugg_Helper_LinkToShowClass extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $class, $showText = '[+]', $hideText = '[-]')
    {
        // set display to none so that the toggle link will not show if JS disabled
        return sprintf('<a href="" id="%1$s-show" style="display:none;">%2$s</a>
<script type="text/javascript">
jQuery("#%1$s-show").css("display", "").click(
  function () {
    jQuery(".%1$s").each(function(){
      jQuery(this).slideDown("fast");
      jQuery("#" + this.id + "-toggle").text("%3$s");
    });
    return false;
  }
);
</script>', $class, h($showText), h($hideText));
    }
}