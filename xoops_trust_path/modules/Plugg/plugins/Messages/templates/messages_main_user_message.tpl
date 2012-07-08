<ul class="messages-actions">
<?php if ($message->isOutgoing()):?>
  <li><?php echo $this->LinkToRemote($this->_('&laquo; Back to Sent messages'), 'plugg-content', array('path' => 'sent', 'params' => array('messages_type' => $message->type)));?></li>
<?php else:?>
  <li><?php echo $this->LinkToRemote($this->_('&laquo; Back to Inbox'), 'plugg-content', array('params' => array('messages_type' => $message->type)));?></li>
<?php endif;?>
</ul>
<div class="messages-message">
  <div class="messages-messages-title"><?php _h($message->title);?></div>
  <dl class="messages-messages-data">
    <dt><?php $message->isOutgoing() ? $this->_e('Sent to:') : $this->_e('Sender:');?></dt><dd><?php echo $this->User_IdentityLink($message_from_to_user);?></dd>
    <dt><?php $message->isOutgoing() ? $this->_e('Sent at:') : $this->_e('Received at:');?></dt><dd><?php echo $this->DateTime($message->created);?></dd>
  </dl>
  <div class="messages-messages-body"><?php echo $message->body_html;?></div>
<?php if ($message->isIncoming()):?>
    <?php echo $this->User_IdentityThumbnail($message_from_to_user);?>
<?php   if ($signature = $message_from_to_user->hasData('Signature', 'default')):?>
    <div class="signature">
      <span>__________________</span><br />
      <?php echo $signature['value'];?>
    </div>
<?php   endif;?>
<?php endif;?>
</div>
<ul class="messages-actions">
<?php if ($identity_is_me && $message->isIncoming()):?>
  <li><?php echo $this->LinkToRemote($this->_('Reply'), 'plugg-messages-replyform', array('path' => $message->id . '/reply'), array(), array('toggle' => 'blind'));?><span> | </span></li>
<?php endif;?>
  <li>
    <form action="<?php echo $this->Url(array('path' => $message->id . '/submit'));?>" method="post">
      <input type="submit" name="submit_action_delete" value="<?php $this->_e('Delete');?>" />
<?php if (!$message->isRead()):?>
      <input type="submit" name="submit_action_read" value="<?php $this->_e('Mark as read');?>" />
<?php else:?>
      <input type="submit" name="submit_action_read" value="<?php $this->_e('Mark as unread');?>" />
<?php endif;?>
<?php if (!$message->isStarred()):?>
      <input type="submit" name="submit_action_star" value="<?php $this->_e('Add star');?>" />
<?php else:?>
      <input type="submit" name="submit_action_star" value="<?php $this->_e('Remove star');?>" />
<?php endif;?>
      <?php echo $this->TokenHtml('messages_message_submit');?>
    </form>
  </li>
</ul>
<div id="plugg-messages-replyform"></div>