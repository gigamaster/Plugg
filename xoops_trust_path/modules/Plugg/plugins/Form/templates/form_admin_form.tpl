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
      <th><?php _h($this->_('Title'));?></th>
      <td><?php echo $this->LinkTo(h($form->title), array('script' => 'main', 'base' => '/form', 'path' => $form->id), array('title' => $this->_('View this form')));?></td>
    </tr>
    <tr>
      <th><?php _h($this->_('Header'));?></th>
      <td><?php echo $form->header_formatted;?></td>
    </tr>
    <tr>
      <th><?php _h($this->_('Date created'));?></th>
      <td><?php echo $this->DateTime($form->created);?></td>
    </tr>
    <tr>
      <th><?php _h($this->_('Entries'));?></th>
      <td><?php echo $form->formentry_count;?></td>
    </tr>
  </tbody>
</table>

<h3><?php $this->_e('Form entries');?></h3>
<div id="plugg-form-admin-form-entries"></div>
<?php $this->ImportRemote('plugg-form-admin-form-entries', $this->Url('/content/form/' . $form->id . '/entries'));?>