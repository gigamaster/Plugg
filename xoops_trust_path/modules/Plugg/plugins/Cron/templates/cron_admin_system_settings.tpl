<?php if (!empty($logs)):?>
<div>
  <code>
<?php   echo implode('<br />', $logs)?>
  </code>
</div>
<?php endif;?>
<?php echo $form_html;?>