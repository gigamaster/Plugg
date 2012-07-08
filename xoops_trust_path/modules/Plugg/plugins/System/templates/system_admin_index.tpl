<table class="plugg-vertical system-status-report">
<?php foreach ($status as $_status):?>
  <tr class="system-status-report">
    <th><?php _h($_status['title']);?></th>
    <td class="plugg-<?php echo $_status['severity'];?>"><?php echo $_status['value'];?></td>
  </tr>
<?php   if (!empty($_status['description'])):?>
  <tr class="system-status-report-description">
    <td colspan="2"><?php echo $_status['description'];?></td>
  </tr>
<?php   endif;?>
<?php endforeach;?>
</table>