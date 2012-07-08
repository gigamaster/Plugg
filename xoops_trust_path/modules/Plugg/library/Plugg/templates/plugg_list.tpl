<ul class="plugg-list <?php _h($this->PluginName(false));?>">
<?php foreach ($list['items'] as $item):?>
<?php   $this->_includeTemplate('plugg_list_item', array('item' => $item));?>
<?php endforeach;?>
</ul>
<div class="clear"></div>
