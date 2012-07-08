<table class="plugg-vertical">
  <thead>
    <tr><td colspan="2"></td></tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="2"></td>
    </tr>
  </tfoot>
  <tbody>
    <tr>
      <th><?php $this->_e('Name');?></th>
      <td><?php _h($auth->title);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Plugin');?></th>
      <td><?php _h($auth_plugin_nicename);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Status');?></th>
      <td><?php if ($auth->active):?><?php $this->_e('Active');?><?php else:?><?php $this->_e('Not active');?><?php endif;?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Accounts');?></th>
      <td><?php echo $auth->authdata_count;?></td>
    </tr>
  </tbody>
</table>

<div id="plugg-user-admin-auths-auth-authdatas"></div>
<?php $this->ImportRemote('plugg-user-admin-auths-auth-authdatas', array('path' => $auth->id . '/authdatas'));?>