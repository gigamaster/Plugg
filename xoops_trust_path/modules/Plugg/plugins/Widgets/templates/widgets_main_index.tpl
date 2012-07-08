<?php if (empty($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_LEFT]) && empty($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_RIGHT]) && $this->User()->isSuperUser()):?>
<div class="plugg-info"><?php printf($this->_('You can add widgets to this page from <a href="%s">here</a>.'), $this->Url(array('script' => 'admin', 'base' => '/system/widgets')));?></div>
<?php endif;?>

<table class="widgets-widgets">
  <tbody>
<?php   foreach ($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_TOP] as $widget):?>
    <tr>
      <td colspan="2" class="widgets-widget-top">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
    <tr>
      <td class="widgets-widgets-left">
<?php   foreach ($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_LEFT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
      <td class="widgets-widgets-right">
<?php   foreach ($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_RIGHT] as $widget):?>
        <div>
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
        </div>
<?php   endforeach;?>
      </td>
    </tr>
<?php   foreach ($widgets[Plugg_Widgets_Plugin::WIDGET_POSITION_BOTTOM] as $widget):?>
    <tr>
      <td colspan="2" class="widgets-widget-bottom">
<?php     $this->_includeTemplate('plugg_widget', array('widget' => $widget));?>
      </td>
    </tr>
<?php   endforeach;?>
  </tbody>
</table>