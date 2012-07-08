<?php $this->FormTag('post', $this->Url($submit_path));?>
<table>
  <thead>
    <tr>
      <th width="30%"><?php echo $this->_('Message');?></th>
      <th width="30%"><?php echo $this->_('Translation');?></th>
      <th><?php echo $this->_('Custom');?></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="3"><input type="submit" value="<?php _h($this->_('Update'));?>" /></td>
    </tr>
  </tfoot>
  <tbody>
<?php if (!empty($original_messages)):?>
<?php   foreach (array_keys($original_messages) as $key):?>
    <tr>
      <td><?php _h($key);?></td>
      <td><?php _h($original_messages[$key]);?></td>
<?php     if (isset($custom_messages[$key])):?>
      <td class="custom"><textarea name="messages[<?php _h($key);?>]"><?php _h($custom_messages[$key]);?></textarea></td>
<?php     else:?>
      <td><textarea style="width:95%; height:95%;" name="messages[<?php _h($key);?>]"></textarea></td>
<?php     endif;?>
    </tr>
<?php   endforeach;?>
<?php else:?>
    <tr><td colspan="3"></td></tr>
<?php endif;?>
  </tbody>
</table>
<?php echo $this->TokenHtml($token_name);?>
</form>