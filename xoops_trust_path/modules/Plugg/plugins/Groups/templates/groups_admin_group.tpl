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
      <td><?php echo $this->LinkTo(h($group->display_name), array('script' => 'main', 'base' => '/groups', 'path' => $group->name));?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Avatar');?></th>
      <td><?php echo $this->Groups_GroupThumbnail($group);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Description');?></th>
      <td><?php echo $group->description_html;?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Created');?></th>
      <td><?php printf('%s by %s', $this->DateTime($group->created), $this->User_IdentityLink($group->User));?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Type');?></th>
      <td><?php echo $group->getTypeStr();?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Members');?></th>
      <td><?php echo $group->getActiveMemberCount();?></td>
    </tr>
  </tbody>
</table>

<h3><?php $this->_e('Active members');?></h3>
<div id="plugg-groups-members"></div>
<?php $this->ImportRemote('plugg-groups-members', array('path' => $group->id . '/members'));?>

<h3><?php $this->_e('Pending membership requests');?></h3>
<div id="plugg-groups-members-pending"></div>
<?php $this->ImportRemote('plugg-groups-members-pending', array('path' => $group->id . '/members/pending'));?>

<h3><?php $this->_e('Membership invitations');?></h3>
<div id="plugg-groups-members-invited"></div>
<?php $this->ImportRemote('plugg-groups-members-invited', array('path' => $group->id . '/members/invited'));?>