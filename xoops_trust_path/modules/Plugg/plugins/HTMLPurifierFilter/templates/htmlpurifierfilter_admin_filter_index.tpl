<div class="nodesSort">
<?php $this->_e('Sort by: ');echo $this->SelectToRemote('sortby', $entity_sort, 'plugg-content', array('order,ASC' => $this->_('Order, ascending'), 'order,DESC' => $this->_('Order, descending'), 'active,ASC' => $this->_('Active, ascending'), 'active,DESC' => $this->_('Active, descending')), array('path' => ''), $this->_('Go'));?>
</div>
<?php $this->FormTag('post', array('path' => 'filter/submit', 'params' => array('sortby' => $entity_sort)));?>
  <table class="horizontal">
    <thead>
      <tr>
        <th><?php $this->_e('Name');?></th>
        <th><?php $this->_e('Plugin');?></th>
        <th><?php $this->_e('Active');?></th>
        <th><?php $this->_e('Order');?></th>
        <th></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td class="center" colspan="5">
          <input type="submit" value="<?php $this->_e('Update');?>" />
        </td>
      </tr>
    </tfoot>
    <tbody>
<?php if ($entity_objects->count() > 0):?>
<?php   foreach ($entity_objects as $e):?>
<?php     $e_id = $e->id; $plugin_nicename = $custom_filter_names[$e->plugin][$e->name]['plugin_nicename'];?>
      <tr>
        <td><?php _h($custom_filter_names[$e->plugin][$e->name]['nicename']);?><br /><small>(<?php _h($e->name);?>)</small></td>
        <td><?php _h($plugin_nicename);?><br /><small>(<?php _h($e->plugin);?>)</small></td>
        <td><input type="checkbox" name="filters[<?php echo $e_id;?>][active]" value="1" <?php if ($e->active):?>checked="checked"<?php endif;?> /></td>
        <td><input type="text" name="filters[<?php echo $e_id;?>][order]" value="<?php echo $e->order;?>" size="4" /></td>
        <td>
          <a href="<?php echo $this->Url(array('base' => '/system', 'path' => 'plugin/' . $e->plugin . '/configure', 'params' => array('return_to' => array('base' => $this->UrlBase()))));?>"><?php $this->_e('Configure');?></a>
<?php     if ($admin_routes = $custom_filter_names[$e->plugin][$e->name]['admin_routes']):?>
          <br />
<?php       foreach ($admin_routes as $admin_route => $admin_route_data):?>
<?php         if (empty($admin_route_data['title']) || $admin_route_data['title'] == $plugin_nicename):?>
<?php           echo $this->LinkToRemote($this->_('Admin'), 'plugg-content', array('path' => $admin_route));?>
<?php         else:?>
<?php           echo $this->LinkToRemote($admin_route_data['title'], 'plugg-content', array('path' => $admin_route));?>
<?php         endif;?>
<?php       endforeach;?>
<?php     endif;?>
        </td>
      </tr>
<?php   endforeach;?>
<?php else:?>
      <tr><td colspan="7"></td></tr>
<?php endif;?>
    </tbody>
  </table>
  <?php echo $this->TokenHtml('htmlpurifierfilter_admin_submit');?>
</form>