<div class="messages-messages">
<?php if ($delete_older_than_days):?>
<div class="plugg-warning"><?php printf($this->_('Messages without a star and older than %d days will be automatically deleted.'), $delete_older_than_days);?></div>
<?php endif;?>
<div class="messages-messages-nav">
<?php foreach (array('all' => $this->_('Show all'), 'read' => $this->_('Read'), 'unread' => $this->_('Unread'), 'starred' => $this->_('Starred'), 'unstarred' => $this->_('Unstarred')/*, 'deleted' => $this->_('Deleted')*/) as $select_k => $select_v):?>
<?php   if ($messages_select != $select_k):?>
  <span><?php echo $this->LinkToRemote($select_v, 'plugg-content', array('path' => 'sent', 'params' => array('messages_select' => $select_k, 'messages_sortby' => $messages_sortby)));?></span>
  <span> | </span>
<?php   else:?>
  <span class="messages-messages-select-current"><?php _h($select_v);?></span>
  <span> | </span>
<?php   endif;?>
<?php endforeach;?>
  <?php $this->_e('Sort by: ');echo $this->SelectToRemote('messages_sortby', $messages_sortby, 'plugg-content', $messages_sortby_allowed, array('path' => 'sent', 'params' => array('messages_select' => $messages_select)), $this->_('Go'));?>
  <span> | </span>
  <span><?php echo $this->LinkToRemote($this->_('Refresh'), 'plugg-content', array('path' => 'sent', 'params' => array('messages_select' => $messages_select, 'messages_sortby' => $messages_sortby, 'time' => time())));?></span>
</div>
<form action="<?php echo $this->Url(array('path' => 'submit'));?>" method="post">
  <table class="plugg-horizontal">
    <thead>
      <tr>
        <th>
          <input id="plugg-messages-checkall" class="checkall plugg-messages-checkall2" type="checkbox" />
          <span><?php $this->_e('Sent to');?></span>
        </th>
        <th colspan="2" width="50%"><?php $this->_e('Message');?></th>
        <th><?php $this->_e('Sent at');?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="2">
          <input id="plugg-messages-checkall2" class="checkall plugg-messages-checkall" type="checkbox" />
          <input type="submit" name="submit_delete" value="<?php $this->_e('Delete');?>">
          <select name="submit_action">
            <option value="read"><?php $this->_e('Mark as read');?></option>
            <option value="unread"><?php $this->_e('Mark as unread');?></option>
            <option value="star"><?php $this->_e('Add star');?></option>
            <option value="unstar"><?php $this->_e('Remove star');?></option>
          </select>
          <input type="submit" value="<?php $this->_e('Update');?>">
        </td>
        <td colspan="2"><?php echo $this->PageNavRemote('plugg-content', $messages_pages, $messages_page->getPageNumber(), array('path' => 'sent', 'params' => array('messages_select' => $messages_select, 'messages_sortby' => $messages_sortby)));?></td>
      </tr>
    </tfoot>
    <tbody>
<?php /*if ($messages_select == 'deleted'):?>
    <tr>
      <td colspan="3" style="text-align:center;"><?php if ($messages->count()):?><a href="<?php echo $this->Url(array('base' => '/message', 'path' => 'remove_deleted'));?>"><?php $this->_e('Remove deleted messagas completely');?></a> <?php endif;?>(<?php $this->_e('messages deleted more than 5 days ago will be automatically deleted');?>)</td>
    </tr>
<?php endif;*/?>
<?php if ($messages->count()):?>

<?php   foreach ($messages->with('FromToUser') as $message): $message_sender = $message->FromToUser;?>
      <tr class="messages-message<?php if ($message->isRead()):?> shadow<?php endif;?>">
        <td class="messages-messages-sender">
          <input type="checkbox" class="plugg-messages-checkall plugg-messages-checkall2" name="messages[]" value="<?php echo $message->id;?>" />
          <img src="<?php echo $this->ImageUrl($this->PluginName(false), $message->isStarred() ? 'star.gif' : 'star_empty.gif');?>" alt="star" />
          <span><?php echo $this->User_IdentityIcon($message_sender);?> <?php echo $this->User_IdentityLink($message_sender);?></span>
        </td>
        <td colspan="2" class="messages-messages-content"><span class="messages-messages-title"><?php echo $this->LinkToRemote(mb_strimlength($message->title, 0, 100), 'plugg-content', array('path' => $message->id));?></span></td>
        <td class="messages-messages-time"><?php echo $this->DateTime($message->created);?></td>
      </tr>
<?php   endforeach;?>
<?php else/*if ($messages_select != 'deleted')*/:?>
      <tr class="messages-message"><td colspan="4" style="text-align:center;"><?php $this->_e('No messages');?></td></tr>
<?php endif;?>
    </tbody>
  </table>
  <input type="hidden" name="messages_type" value="<?php echo Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING;?>" />
  <?php echo $this->TokenHtml('messages_messages_submit');?>
</form>
</div>