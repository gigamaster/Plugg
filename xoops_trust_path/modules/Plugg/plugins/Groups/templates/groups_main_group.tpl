<div class="groups-summary">
  <div class="groups-images">
    <div class="groups-image">
      <?php echo $this->Groups_GroupAvatar($group);?> 
      <ul class="groups-stat">
        <li><span class="groups-stat-label"><?php $this->_e('Created: ');?></span><?php echo $this->DateTime($group->created, true);?></li>
        <li><span class="groups-stat-label"><?php $this->_e('Founder: ');?></span><?php echo $this->User_IdentityLink($group->User);?></li>
      </ul>
    </div>
  </div>

  <div class="groups-profile">
    <table class="plugg-vertical">
      <caption><?php $this->_e('Group information');?></caption>
      <tbody>
        <tr>
          <th><?php $this->_e('About');?></th>
          <td><?php echo $group->description_html;?></td>
        </tr>
        <tr>
          <th><?php $this->_e('Group type');?></th>
          <td><?php echo $group->getTypeStr();?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div style="clear:both;"></div>

<table class="groups-widgets">
  <tbody>
<?php   foreach ($widgets[Plugg_Groups_Plugin::WIDGET_POSITION_TOP] as $widget):?>
    <tr>
      <td colspan="2" class="groups-widgets-top">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
    <tr>
      <td class="groups-widgets-left">
<?php   foreach ($widgets[Plugg_Groups_Plugin::WIDGET_POSITION_LEFT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
      <td class="groups-widgets-right">
<?php   foreach ($widgets[Plugg_Groups_Plugin::WIDGET_POSITION_RIGHT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
    </tr>
<?php   foreach ($widgets[Plugg_Groups_Plugin::WIDGET_POSITION_BOTTOM] as $widget):?>
    <tr>
      <td colspan="2" class="groups-widgets-bottom">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
  </tbody>
</table>