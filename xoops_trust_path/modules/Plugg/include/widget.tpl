<div class="plugg-xoopscube-widget <?php _h($widget['name']);?>" id="plugg-xoopscube-widget-<?php _h($widget['plugin']);?>-<?php _h($widget['name']);?>">
<?php if (!is_array($widget['content'])):?>
  <?php echo $widget['content'];?>
<?php else:?>
  <ul class="plugg-xoopscube-widget-entries">
<?php   foreach ($widget['content'] as $widget_content):?>
<?php     if (!empty($widget_content['icon'])):?>
    <li class="plugg-xoopscube-widget-entry plugg-xoopscube-widget-entry-with-icon">
      <div class="plugg-xoopscube-widget-entry-icon">
<?php       if (!empty($widget_content['icon_link'])):?>
        <a href="<?php echo $widget_content['icon_link'];?>" title="<?php if (isset($widget_content['icon_title'])):?><?php _h($widget_content['icon_title']);?><?php endif;?>"><img src="<?php echo $widget_content['icon'];?>" width="16" alt="" /></a>
<?php       else:?>
        <img src="<?php echo $widget_content['icon'];?>" width="16" alt="<?php if (isset($widget_content['icon_title'])):?><?php _h($widget_content['icon_title']);?><?php endif;?>" />
<?php       endif;?>
      </div>
<?php     else:?>
    <li class="plugg-xoopscube-widget-entry">
<?php     endif;?>
      <div class="plugg-xoopscube-widget-entry-content">
<?php     if (isset($widget_content['title'])):?>
        <div class="plugg-xoopscube-widget-entry-title"><a title="<?php _h($widget_content['title']);?>" href="<?php echo is_array($widget_content['url']) ? $this->Url($widget_content['url']) : h($widget_content['url']);?>"><?php _h(mb_strimlength($widget_content['title'], 0, 100));?></a></div>
<?php     endif;?>
<?php     if (isset($widget_content['body'])):?>
        <p class="plugg-xoopscube-widget-entry-body"><?php _h(mb_strimlength(strip_tags(strtr($widget_content['body'], array("\r" => '', "\n" => ''))), 0, 200));?></p>
<?php     elseif(isset($widget_content['body_html'])):?>
        <div><?php echo $widget_content['body_html'];?></div>
<?php     endif;?>
        <ul class="plugg-xoopscube-widget-entry-meta">
<?php     if (!empty($widget_content['user'])):?>
          <li><?php if (isset($widget_content['timestamp_label'])): _h($widget_content['timestamp_label']);?>: <?php endif;?><?php printf($this->_('%s by %s'), $this->DateTime($widget_content['timestamp']), $this->User_IdentityLink($widget_content['user']));?></li>
<?php     else:?>
          <li><?php if (isset($widget_content['timestamp_label'])): _h($widget_content['timestamp_label']);?>: <?php endif;?><?php echo $this->DateTime($widget_content['timestamp'])?></li>
<?php     endif;?>
<?php     if (!empty($widget_content['meta_html'])):?>
<?php       foreach ($widget_content['meta_html'] as $meta_html):?>
          <li><?php echo $meta_html;?></li>
<?php       endforeach;?>
<?php     endif;?>
        </ul>
      </div>
    </li>
<?php   endforeach;?>
  </ul>
<?php endif;?>
</div>
<div class="clear"></div>