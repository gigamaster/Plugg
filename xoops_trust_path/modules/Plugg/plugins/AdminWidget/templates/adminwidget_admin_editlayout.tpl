<noscript>
  <div class="plugg-error"><?php $this->_e('This page requires JavaScript enabled in your browser.');?></div>
</noscript>
<div class="plugg-info">
<?php $this->_e('Choose the widgets you want to add by dragging them from the widgets list on the right to the area on the left and position them in the order you would like them to appear.');?>
</div>
<table class="widgets" style="width:821px;">
  <tr>
    <td width="60%" align="center" valign="top">
      <h4 class="widgets-title"><?php $this->_e('Active widgets');?></h4>
<?php $this->FormTag('post', $this->Url($widgets_submit_path), array('id' => $widgets_form_id));?>
      <table>
        <tr>
          <td colspan="2" class="widgets-active widgets-active-top">
            <input type="hidden" name="widgets[TOP]" value="0" />
            <ul class="widgets-widgets widgets-widgets-top">
<?php foreach ($widgets_top as $widget):?>
              <li class="widgets-widget widgets-widget-active">
                <div class="widgets-widget-control"><?php echo $this->LinkToRemote($this->_('edit'), $widgets_form_id . $widget['id'], $this->Url(sprintf($widgets_edit_widget_path, $widget['id'])), array(), array('slide' => true, 'toggle' => true));?></div>
                <div class="widgets-widget-title"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($widget['title']);?></span></div>
                <input type="hidden" name="widgets[]" value="<?php echo $widget['id'];?>" />
                <div class="widgets-widget-details">
                  <div class="widgets-widget-summary"><?php _h($widget['summary']);?></div>
                  <div class="widgets-widget-form" id="<?php echo $widgets_form_id . $widget['id'];?>"></div>
                </div>
              </li>
<?php endforeach;?>
            </ul>
          </td>
        </tr>
        <tr>
          <td width="50%" valign="top" class="widgets-active">
            <input type="hidden" name="widgets[LEFT]" value="0" />
            <ul class="widgets-widgets">
<?php foreach ($widgets_left as $widget):?>
              <li class="widgets-widget widgets-widget-active">
                <div class="widgets-widget-control"><?php echo $this->LinkToRemote($this->_('edit'), $widgets_form_id . $widget['id'], $this->Url(sprintf($widgets_edit_widget_path, $widget['id'])), array(), array('slide' => true, 'toggle' => true));?></div>
                <div class="widgets-widget-title"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($widget['title']);?></span></div>
                <input type="hidden" name="widgets[]" value="<?php echo $widget['id'];?>" />
                <div class="widgets-widget-details">
                  <div class="widgets-widget-summary"><?php _h($widget['summary']);?></div>
                  <div class="widgets-widget-form" id="<?php echo $widgets_form_id . $widget['id'];?>"></div>
                </div>
              </li>
<?php endforeach;?>
            </ul>
          </td>
          <td width="50%" valign="top" class="widgets-active">
            <input type="hidden" name="widgets[RIGHT]" value="0" />
            <ul class="widgets-widgets">
<?php foreach ($widgets_right as $widget):?>
              <li class="widgets-widget widgets-widget-active">
                <div class="widgets-widget-control"><?php echo $this->LinkToRemote($this->_('edit'), $widgets_form_id . $widget['id'], $this->Url(sprintf($widgets_edit_widget_path, $widget['id'])), array(), array('slide' => true, 'toggle' => true));?></div>
                <div class="widgets-widget-title"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($widget['title']);?></span></div>
                <input type="hidden" name="widgets[]" value="<?php echo $widget['id'];?>" />
                <div class="widgets-widget-details">
                  <div class="widgets-widget-summary"><?php _h($widget['summary']);?></div>
                  <div class="widgets-widget-form" id="<?php echo $widgets_form_id . $widget['id'];?>"></div>
                </div>
              </li>
<?php endforeach;?>
            </ul>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="widgets-active widgets-active-bottom">
            <input type="hidden" name="widgets[BOTTOM]" value="0" />
            <ul class="widgets-widgets widgets-widgets-bottom">
<?php foreach ($widgets_bottom as $widget):?>
              <li class="widgets-widget widgets-widget-active">
                <div class="widgets-widget-control"><?php echo $this->LinkToRemote($this->_('edit'), $widgets_form_id . $widget['id'], $this->Url(sprintf($widgets_edit_widget_path, $widget['id'])), array(), array('slide' => true, 'toggle' => true));?></div>
                <div class="widgets-widget-title"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($widget['title']);?></span></div>
                <input type="hidden" name="widgets[]" value="<?php echo $widget['id'];?>" />
                <div class="widgets-widget-details">
                  <div class="widgets-widget-summary"><?php _h($widget['summary']);?></div>
                  <div class="widgets-widget-form" id="<?php echo $widgets_form_id . $widget['id'];?>"></div>
                </div>
              </li>
<?php endforeach;?>
            </ul>
          </td>
        </tr>
      </table>
      <?php echo $this->TokenHtml($widgets_submit_token_id);?>
      </form>
    </td>
    <td width="40%" class="widgets-available" valign="top">
      <h4 class="widgets-title"><?php $this->_e('Available widgets');?></h4>
      <ul class="widgets-widgets">
<?php foreach (array_keys($widgets) as $widget_id):?>
<?php   $widget = $widgets[$widget_id];?>
        <li class="widgets-widget">
          <div class="widgets-widget-control"><?php echo $this->LinkToRemote($this->_('edit'), $widgets_form_id . $widget_id, $this->Url(sprintf($widgets_edit_widget_path, $widget['id'])), array(), array('slide' => true, 'toggle' => true));?></div>
          <div class="widgets-widget-title"><a href="#" class="draggableHandle">&nbsp;</a><span><?php _h($widget['title']);?></span></div>
          <input type="hidden" name="widgets[]" value="<?php echo $widget_id;?>" />
          <div class="widgets-widget-details">
            <div class="widgets-widget-summary"><?php _h($widget['summary']);?></div>
            <div class="widgets-widget-form" id="<?php echo $widgets_form_id . $widget_id;?>"></div>
          </div>
        </li>
<?php endforeach;?>
      </ul>
    </td>
  </tr>
</table>