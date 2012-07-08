<?php   if (!empty($item['image'])):?>
  <li class="plugg-item plugg-item-with-image<?php if (isset($item['class'])):?> <?php _h($item['class']);?><?php endif;?>"<?php if (isset($item['id'])):?> id="<?php _h($item['id']);?>"<?php endif;?>>
    <div class="plugg-item-image">
<?php     if (!empty($item['image_link'])):?>
      <a href="<?php echo $item['image_link'];?>" title="<?php if (isset($item['image_title'])):?><?php _h($item['image_title']);?><?php endif;?>"><img src="<?php echo $item['image'];?>" width="100" alt="" /></a>
<?php     else:?>
      <img src="<?php echo $item['image'];?>" width="100" alt="<?php if (isset($item['image_title'])):?><?php _h($item['image_title']);?><?php endif;?>" />
<?php     endif;?>
    </div>
<?php   elseif (!empty($item['thumbnail'])):?>
  <li class="plugg-item plugg-item-with-thumbnail<?php if (isset($item['class'])):?> <?php _h($item['class']);?><?php endif;?>"<?php if (isset($item['id'])):?> id="<?php _h($item['id']);?>"<?php endif;?>>
    <div class="plugg-item-image plugg-item-thumbnail">
<?php     if (!empty($item['thumbnail_link'])):?>
      <a href="<?php echo $item['thumbnail_link'];?>" title="<?php if (isset($item['thumbnail_title'])):?><?php _h($item['thumbnail_title']);?><?php endif;?>"><img src="<?php echo $item['thumbnail'];?>" width="48" alt="" /></a>
<?php     else:?>
      <img src="<?php echo $item['thumbnail'];?>" width="100" alt="<?php if (isset($item['thumbnail_title'])):?><?php _h($item['thumbnail_title']);?><?php endif;?>" />
<?php     endif;?>
    </div>
<?php   else:?>
  <li class="plugg-item<?php if (isset($item['class'])):?> <?php _h($item['class']);?><?php endif;?>"<?php if (isset($item['id'])):?> id="<?php _h($item['id']);?>"<?php endif;?>>
<?php   endif;?>

    <div class="plugg-item-content">
<?php   $this->_includeTemplate('plugg_list_item_content', array('item' => $item));?>
    </div>

<?php   if (!empty($item['links'])):?>
    <ul class="plugg-menu plugg-item-links">
<?php     foreach ($item['links'] as $link):?>
      <li>
<?php       if (empty($link['ajax'])):?>
        <?php echo $this->LinkTo($link['text'], $link['url'], @$link['attributes']);?>
<?php       else:?>
        <?php echo $this->LinkToRemote($link['text'], $link['ajaxTarget'], $link['url'], (array)@$link['ajaxUrl'], (array)@$link['ajaxOptions'], @$link['attributes']);?>
<?php       endif;?>
      </li>
<?php     endforeach;?>
    </ul>
<?php   endif;?>

<?php if (!empty($item['extra_html'])):?>
    <div class="plugg-item-extra">
      <?php echo $item['extra_html'];?>
    </div>
<?php endif;?>

  </li>