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
      <td><?php _h($role->display_name);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Created');?></th>
      <td><?php _h($this->DateTime($role->created));?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Members');?></th>
      <td><?php echo $role->member_count;?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Permissions');?></th>
      <td>
        <dl id="rolePermissions" style="margin-bottom:5px;">
<?php $role_permissions = $role->getPermissions();?>
<?php foreach (array_keys($permissions) as $perm_group):?>
          <dt><?php _h($perm_group);?><dt>
<?php   foreach ($permissions[$perm_group] as $perm => $perm_label):?>
<?php     if (!empty($role_permissions[$perm_group]) && in_array($perm, $role_permissions[$perm_group])):?>
          <dd style="color: #000;"><?php _h($perm_label);?></dd>
<?php     else:?>
          <dd style="color: #ccc;"><?php _h($perm_label);?></dd>
<?php     endif;?>
<?php   endforeach;?>
<?php endforeach;?>
        </dl>
        <div><?php echo $this->LinkToToggle('rolePermissions', true, $this->_('Hide list'), $this->_('Show list'));?></div>
      </td>
    </tr>
  </tbody>
</table>
<h3><?php $this->_e('Listing members');?></h3>
<div id="plugg-user-admin-roles-role-members"></div>
<?php $this->ImportRemote('plugg-user-admin-roles-role-members', array('path' => $role->id . '/members'));?>