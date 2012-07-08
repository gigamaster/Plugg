<?php if (isset($item['title'])):?>
      <div class="plugg-item-title">
<?php   if (!empty($item['url'])):?>
        <a href="<?php _h($item['url']);?>"><?php _h($item['title']);?></a>
<?php   else:?>
      <?php _h($item['title']);?>
<?php   endif;?>
      </div>
<?php elseif (isset($item['title_html'])):?>
    <?php echo $item['title_html'];?>
<?php endif;?>
      <ul class="plugg-menu plugg-item-meta">
<?php if (!empty($item['timestamp'])):?>
<?php   if (!empty($item['user'])):?>
        <li><?php if (isset($item['timestamp_label'])): _h($item['timestamp_label']);?><?php endif;?><?php printf($this->_('%s by %s'), $this->DateTime($item['timestamp']), $this->User_IdentityLink($item['user']));?></li>
<?php   else:?>
        <li><?php if (isset($item['timestamp_label'])): _h($item['timestamp_label']);?><?php endif;?><?php echo $this->DateTime($item['timestamp'])?></li>
<?php   endif;?>
<?php endif;?>
<?php if (!empty($item['meta_html'])):?>
<?php   foreach ($item['meta_html'] as $meta_html):?>
        <li><?php echo $meta_html;?></li>
<?php   endforeach;?>
<?php endif;?>
      </ul>

<?php if (isset($item['body'])):?>
      <p class="plugg-item-body"><?php _h($item['body']);?></p>
<?php elseif(isset($item['body_html'])):?>
      <div class="plugg-item-body"><?php echo $item['body_html'];?></div>
<?php endif;?>

<?php if (!empty($item['attachments'])):?>
      <div class="plugg-item-attachments">
<?php   if (!empty($item['attachments']['images'])):?>
        <ul class="plugg-item-attachments-images">
<?php     foreach ($item['attachments']['images'] as $attachment):?>
          <li>
<?php       if (isset($attachment['thumbnail_url'])):?>
            <a href="<?php _h($attachment['url']);?>" class="colorbox" rel="colorbox-<?php echo $item['id']?>" title="<?php _h($attachment['name']);?>">
              <img src="<?php _h($attachment['thumbnail_url']);?>" alt=""<?php if (!empty($attachment['thumbnail_height'])):?> height="<?php echo intval($attachment['thumbnail_height']);?>"<?php endif;?><?php if (!empty($attachment['thumbnail_width'])):?> width="<?php echo intval($attachment['thumbnail_width']);?>"<?php endif;?> />
            </a>
<?php       else:?>
            <a href="<?php _h($attachment['url']);?>" class="colorbox" rel="colorbox-<?php echo $item['id']?>" title="<?php _h($attachment['name']);?>">
              <img src="<?php _h($attachment['url']);?>" alt=""<?php if (!empty($attachment['image_height'])):?> height="<?php echo intval($attachment['image_height']);?>"<?php endif;?><?php if (!empty($attachment['image_width'])):?> width="<?php echo intval($attachment['image_width']);?>"<?php endif;?> />
            </a>
<?php       endif;?>
          </li>
<?php     endforeach;?>
        </ul>
<?php   endif;?>
<?php   if (!empty($item['attachments']['files'])):?>
        <ul class="plugg-item-attachments-files">
<?php     foreach ($item['attachments']['files'] as $attachment):?>
          <li>
            <a href="<?php _h($attachment['url']);?>"><span><?php _h($attachment['name']);?></a> (<?php echo $attachment['size'] >= 1024 * 1024 ? sprintf('%dMB', $attachment['size'] / 1024 * 1024) : ($attachment['size'] >= 1024 ? sprintf('%dKB', $attachment['size'] / 1024) : sprintf('%dB', $attachment['size']));?>)</span>
          </li>
<?php     endforeach;?>
        </ul>
<?php   endif;?>
      </div>
<?php endif;?>