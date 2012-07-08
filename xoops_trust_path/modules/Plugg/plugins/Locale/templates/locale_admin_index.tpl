<h3><?php echo $this->_('Global message cataglogue');?></h3>
<table class="plugg-horizontal">
  <thead>
    <tr>
      <th><?php echo $this->_('Messages');?></th>
      <th></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="2"></td>
    </tr>
  </tfoot>
  <tbody>
    <tr>
      <td><?php if (isset($custom_message_count['plugg'])):?><?php echo $custom_message_count['plugg'];?><?php else:?>0<?php endif;?>/<?php echo $global_message_count;?></td>
      <td><a href="<?php echo $this->Url('/system/settings/locale/messages');?>"><?php $this->_e('Edit');?></a></td>
    </tr>
  </tbody>
</table>

<h3><?php echo $this->_('Plugin message cataglogues');?></h3>
<table class="plugg-horizontal">
  <thead>
    <tr>
      <th><?php echo $this->_('Plugin');?></th>
      <th><?php echo $this->_('Messages');?></th>
      <th></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="3"></td>
    </tr>
  </tfoot>
  <tbody>
<?php foreach (array_keys($plugins) as $k):?>
    <tr>
      <td><?php _h($plugins[$k]['nicename']);?> - <?php _h($k);?></td>
      <td><?php if (isset($custom_message_count[$k])):?><?php echo $custom_message_count[$k];?><?php else:?>0<?php endif;?>/<?php echo $plugin_message_count[$k];?></td>
      <td><a href="<?php echo $this->Url('/system/settings/locale/messages/' . $k);?>"><?php $this->_e('Edit');?></a></td>
    </tr>
<?php endforeach;?>
  </tbody>
</table>