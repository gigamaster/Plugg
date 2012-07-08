<div class="user-summary">
  <div class="user-images">
    <div class="user-image">
      <?php echo $this->User_IdentityAvatar($identity);?>
<?php if ($show_edit_image_link):?>
      <span class="user-image-edit"><a href="<?php echo $this->User_IdentityUrl($identity, 'edit/image');?>"><?php $this->_e('Edit image');?></a></span>
<?php endif;?>
      <ul class="user-stat">
        <li><span class="user-stat-label"><?php $this->_e('User since: ');?></span><?php echo $this->DateTime($identity->created, true);?></li>
        <li><span class="user-stat-label"><?php $this->_e('Last login: ');?></span><?php if ($stat && ($last_login = $stat->last_login)): echo $this->DateTime($last_login, true); else: $this->_e('N/A'); endif;?></li>
      </ul>
    </div>
    <div class="user-status-point"></div>
    <div class="user-status">
<?php if ($identity_is_me || $this->User()->hasPermission('user status edit any')):?>
      <div id="plugg-user-main-identity-viewstatus">
<?php   if ($status && strlen($status->text_filtered)):?>
        <?php echo $status->text_filtered;?>
<?php   else:?>
        <p><?php $this->_e('What are you doing?');?></p>
<?php   endif;?>
      </div>
      <div class="user-status-edit"><?php echo $this->LinkToRemote($this->_('Edit'), 'plugg-user-main-identity-viewstatus', $this->User_IdentityUrl($identity, 'edit/status'));?></div>
<?php else:?>
<?php   if (!$status || empty($status_text)):?>
      <p>...</p>
<?php   endif;?>
<?php endif;?>
    </div>
  </div>
  <div class="user-profile">
<?php echo $profile_html;?>
  </div>
</div>
<div style="clear:both;"></div>
<table class="user-widgets">
  <tbody>
<?php   foreach ($widgets[Plugg_User_Plugin::WIDGET_POSITION_TOP] as $widget):?>
    <tr>
      <td colspan="2" class="user-widgets-top">
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
      </td>
    </tr>
<?php   endforeach;?>
    <tr>
      <td class="user-widgets-left">
<?php   foreach ($widgets[Plugg_User_Plugin::WIDGET_POSITION_LEFT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
      <td class="user-widgets-right">
<?php   foreach ($widgets[Plugg_User_Plugin::WIDGET_POSITION_RIGHT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
    </tr>
<?php   foreach ($widgets[Plugg_User_Plugin::WIDGET_POSITION_BOTTOM] as $widget):?>
    <tr>
      <td colspan="2" class="user-widgets-bottom">
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
      </td>
    </tr>
<?php   endforeach;?>
  </tbody>
</table>