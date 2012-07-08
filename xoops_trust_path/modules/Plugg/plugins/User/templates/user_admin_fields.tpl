<noscript>
  <div class="plugg-error"><?php $this->_e('This page requires JavaScript enabled in your browser.');?></div>
</noscript>
<div class="plugg-info">
<?php $this->_e('Choose the fields you want to add as profile fields by dragging them from the fields list on the right to the area on the left and position them in the order you would like them to appear.');?>
</div>
<table class="form-fields"">
  <tr>
    <td class="form-fields-active">
      <h4 class="form-fields-title"><?php $this->_e('Active fields');?></h4>
      <?php $this->FormTag('post', $this->Url($form_submit_path), array('id' => $form_id));?>
        <div class="form-fields-fieldsets">
<?php foreach ($fields[0] as $fieldset):?>
          <div class="form-fields-fieldset">
            <div class="form-fields-fieldset-control">
              <a href="#" class="form-fields-fieldset-edit"><?php $this->_e('edit');?></a>
<?php   if ($fieldset->name !== 'default'):?>
              <a href="#" class="form-fields-fieldset-delete"><?php $this->_e('delete');?></a>
<?php   endif;?>
              <a href="#" class="form-fields-fieldset-undodelete"><?php $this->_e('undo delete');?></a>
            </div>
            <div class="form-fields-fieldset-label"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($fieldset->title);?></span></div>
            <input class="form-fields-fieldset-id" type="hidden" name="fields[]" value="<?php echo $fieldset->id;?>" />
            <div class="form-fields-fieldset-form" id="<?php echo $form_id . $fieldset->id;?>"></div>
            <dl class="form-fields-fields">
<?php   if (!empty($fields[$fieldset->id])):?>
<?php     foreach ($fields[$fieldset->id] as $field):?>
              <dt class="form-fields-field form-fields-field-active">
                <div class="form-fields-field-control">
                  <a href="#" class="form-fields-field-edit"><?php $this->_e('edit');?></a>
                  <a href="#" class="form-fields-field-delete"><?php $this->_e('delete');?></a>
                  <a href="#" class="form-fields-field-undodelete"><?php $this->_e('undo delete');?></a>
                </div>
                <div class="form-fields-field-label"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h(mb_strimlength($field->title, 0, 30));?></span></div>
                <input class="form-fields-field-id" type="hidden" name="fields[]" value="<?php echo $field->id;?>" />
                <input class="form-fields-field-type" type="hidden" value="<?php echo $field->field_id;?>" />
                <div class="form-fields-field-form" id="<?php echo $form_id . $field->id;?>"></div>
              </dt>
<?php     endforeach;?>
<?php   endif;?>
            </dl>
          </div>
<?php endforeach;?>
        </div>
        <a href="#" id="plugg-form-fields-fieldset-add"><?php $this->_e('Add fieldset');?></a>
        <input type="submit" value="<?php $this->_e('Save layout');?>" id="plugg-form-fields-submit" />
        <?php echo $this->TokenHtml($form_submit_token_id);?>
      </form>
    </td>
    <td class="form-fields-available">
      <h4 class="form-fields-title"><?php $this->_e('Available fields');?></h4>
<?php while (count($field_data)): $i = 0;?>
      <dl class="form-fields-fields">
<?php   while ($i < 10 && ($field = array_shift($field_data))): ++$i;?>
<?php     $field_html_id = $form_id . '-' . $field['plugin'] . '-' . $field['type'];?>
        <dt class="form-fields-field form-fields-field-new" id="<?php echo $field_html_id;?>">
          <div class="form-fields-field-control">
            <a href="#" class="form-fields-field-edit"><?php $this->_e('edit');?></a>
            <a href="#" class="form-fields-field-delete"><?php $this->_e('delete');?></a>
            <a href="#" class="form-fields-field-undodelete"><?php $this->_e('undo delete');?></a>
          </div>
          <div class="form-fields-field-label"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($field['title']);?></span></div>
          <input class="form-fields-field-id" type="hidden" name="fields[]" value="" />
          <input class="form-fields-field-type" type="hidden" value="<?php echo $field['id'];?>" />
          <div class="form-fields-field-form"></div>
        </dt>
        <dd><?php _h($field['summary']);?></dd>
<?php   endwhile;?>
      </dl>
<?php endwhile;?>
    </td>
  </tr>
</table>
<div class="form-fields-fieldset form-fields-fieldset-new" id="plugg-form-fields-fieldset-template">
  <div class="form-fields-fieldset-control">
    <a href="#" class="form-fields-fieldset-edit"><?php $this->_e('edit');?></a>
    <a href="#" class="form-fields-fieldset-delete"><?php $this->_e('delete');?></a>
    <a href="#" class="form-fields-fieldset-undodelete"><?php $this->_e('undo delete');?></a>
  </div>
  <div class="form-fields-fieldset-label"><a href="#" class="draggableHandle">&nbsp;</a><span></span></div>
  <input class="form-fields-fieldset-id" type="hidden" name="fields[]" value="" />
  <div class="form-fields-fieldset-form" id="<?php echo $form_id . '-Form-group';?>"></div>
  <dl class="form-fields-fields"></dl>
</div>