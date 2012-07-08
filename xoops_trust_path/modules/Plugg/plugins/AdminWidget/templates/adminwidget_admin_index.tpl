<table class="adminwidget-widgets">
  <tbody>
<?php   foreach ($widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_TOP] as $widget):?>
    <tr>
      <td colspan="2" class="adminwidget-widgets-top">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
    <tr>
      <td class="adminwidget-widgets-left">
<?php   foreach ($widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_LEFT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
      <td class="adminwidget-widgets-right">
<?php   foreach ($widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_RIGHT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
    </tr>
<?php   foreach ($widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_BOTTOM] as $widget):?>
    <tr>
      <td colspan="2" class="adminwidget-widgets-bottom">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
  </tbody>
</table>