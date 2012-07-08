<table class="plugg-vertical">
  <caption><?php $this->_e('Basic information');?></caption>
  <tbody>
<?php if (in_array(1, (array)$config['name']) && $fields['name']):?>
  <tr>
    <th><?php $this->_e('Full name');?></th>
    <td><?php _h($fields['name']);?></td>
  </tr>
<?php endif;?>

<?php if ($fields['user_viewemail']):?>
  <tr>
    <th><?php $this->_e('Email');?></th>
    <td><?php _h($fields['email']);?></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['url']) && $fields['url']):?>
  <tr>
    <th><?php $this->_e('Website');?></th>
    <td><a rel="me" href="<?php echo $fields['url'];?>"><?php _h($fields['url']);?></a></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['imAccounts'])):?>
<?php   if ($fields['user_icq']):?>
  <tr>
    <th><?php $this->_e('ICQ');?></th>
    <td><?php _h($fields['user_icq']);?></td>
  </tr>
<?php   endif;?>
<?php   if ($fields['user_aim']):?>
  <tr>
    <th><?php $this->_e('AOL Instant Messenger');?></th>
    <td><?php _h($fields['user_aim']);?></td>
  </tr>
<?php   endif;?>
<?php   if ($fields['user_yim']):?>
  <tr>
    <th><?php $this->_e('Yahoo! Messenger');?></th>
    <td><?php _h($fields['user_yim']);?></td>
  </tr>
<?php   endif;?>
<?php   if ($fields['user_msnm']):?>
  <tr>
    <th><?php $this->_e('MSN Messenger');?></th>
    <td><?php _h($fields['user_msnm']);?></td>
  </tr>
<?php   endif;?>
<?php endif;?>

<?php if (!empty($config['enableStatFields'])):?>
  <tr>
    <th><?php $this->_e('Member since');?></th>
    <td><?php echo $this->DateTime($fields['user_regdate']);?></td>
  </tr>
<?php   if ($fields['last_login']):?>
  <tr>
    <th><?php $this->_e('Last login');?></th>
    <td><?php echo $this->DateTime($fields['last_login']);?></td>
  </tr>
<?php   endif;?>
  <tr>
    <th><?php $this->_e('Comments/Posts');?></th>
    <td><?php _h($fields['posts']);?></td>
  </tr>
  <tr>
    <th><?php $this->_e('Rank');?></th>
    <td><?php _h($fields['rank']);?></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['location']) && $fields['user_from']):?>
  <tr>
    <th><?php $this->_e('Location');?></th>
    <td><?php _h($fields['user_from']);?></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['occupation']) && $fields['user_occ']):?>
  <tr>
    <th><?php $this->_e('Occupation');?></th>
    <td><?php _h($fields['user_occ']);?></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['interests']) && $fields['user_intrest']):?>
  <tr>
    <th><?php $this->_e('Interests');?></th>
    <td><?php _h($fields['user_intrest']);?></td>
  </tr>
<?php endif;?>

<?php if (in_array(1, (array)$config['extraInfo']) && $fields['bio']):?>
  <tr>
    <th><?php $this->_e('Extra information');?></th>
    <td><?php _h($fields['bio']);?></td>
  </tr>
<?php endif;?>
</table>

<?php foreach ($extra_fields as $fieldset):?>
<?php   if (!empty($fieldset['fields'])):?>
<table class="plugg-vertical">
<?php     if (isset($fieldset['title'])):?>
  <caption><?php _h($fieldset['title']);?></caption>
<?php     endif;?>
  <thead><tr><td colspan="2"></td></tr></thead>
  <tfoot><tr><td colspan="2"></td></tr></tfoot>
  <tbody>
<?php     foreach ($fieldset['fields'] as $field):?>
<?php       if (isset($field['html'])):?>
    <tr>
      <th><?php _h($field['title']);?></th>
      <td><?php echo $field['html'];?></td>
    </tr>
<?php       endif;?>
<?php     endforeach;?>
  </tbody>
</table>
<?php   endif;?>
<?php endforeach;?>