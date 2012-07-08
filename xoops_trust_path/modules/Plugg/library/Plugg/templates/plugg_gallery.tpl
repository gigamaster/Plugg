<ul class="plugg-gallery <?php _h($this->PluginName(false));?>">
<?php foreach ($gallery['images'] as $image):?>
  <li>
    <span class="plugg-gallery-image">
<?php if (isset($image['original_url'])):?>
      <a href="<?php _h($image['original_url']);?>" class="colorbox" title="<?php _h($image['title']);?>"<?php if (isset($image['rel'])):?> rel="<?php _h($image['rel']);?>"<?php endif;?>><img src="<?php _h($image['url']);?>" alt=""<?php if (!empty($image['height'])):?> height="<?php echo intval($image['height']);?>"<?php endif;?><?php if (!empty($image['width'])):?> width="<?php echo intval($image['width']);?>"<?php endif;?> /></a>
<?php elseif (isset($image['link'])):?>
      <a href="<?php _h($image['link']);?>" title="<?php _h($image['title']);?>"<?php if (isset($image['rel'])):?> rel="<?php _h($image['rel']);?>"<?php endif;?>><img src="<?php _h($image['url']);?>" alt=""<?php if (!empty($image['height'])):?> height="<?php echo intval($image['height']);?>"<?php endif;?><?php if (!empty($image['width'])):?> width="<?php echo intval($image['width']);?>"<?php endif;?> /></a>
<?php else:?>
      <img src="<?php _h($image['url']);?>" alt=""<?php if (!empty($image['height'])):?> height="<?php echo intval($image['height']);?>"<?php endif;?><?php if (!empty($image['width'])):?> width="<?php echo intval($image['width']);?>"<?php endif;?> />
<?php endif;?>
    </span>
    <span class="plugg-gallery-image-caption"><?php if (isset($image['caption_html'])):?><?php echo $image['caption_html'];?><?php else:?><?php _h($image['caption']);?><?php endif;?></span>
  </li>
<?php endforeach;?>
</ul>
<div class="clear"></div>