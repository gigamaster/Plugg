<div class="plugg-widget <?php _h($widget['name']);?>" id="plugg-widget-<?php _h($widget['plugin']);?>-<?php _h($widget['name']);?>">
  <div class="plugg-widget-title"><?php _h($widget['title']);?></div>
  <div class="plugg-widget-content">
<?php if (!empty($widget['menu'])):?>
    <ul class="plugg-menu plugg-widget-menu">
<?php   foreach ($widget['menu'] as $widget_menu):?>
<?php     if (empty($widget_menu['current'])):?>
      <li><?php echo $this->LinkToRemote($widget_menu['text'], 'plugg-widget-' . $widget['plugin'] . '-' . $widget['name'], $this->Url($widget['path'], $widget_menu['settings']), array(), array('updateParent' => true));?></li>
<?php     else:?>
      <li><?php _h($widget_menu['text']);?></li>
<?php     endif;?>
<?php   endforeach;?>
    </ul>
<?php endif;?>
<?php if (!is_array($widget['content'])):?>
    <?php echo $widget['content'];?>
<?php else:?>
    <ul class="plugg-widget-entries">
<?php   foreach ($widget['content'] as $widget_content):?>
<?php     if (!empty($widget_content['icon'])):?>
      <li class="plugg-widget-entry plugg-widget-entry-with-icon">
        <div class="plugg-widget-entry-icon">
<?php       if (!empty($widget_content['icon_link'])):?>
          <a href="<?php echo $widget_content['icon_link'];?>" title="<?php if (isset($widget_content['icon_title'])):?><?php _h($widget_content['icon_title']);?><?php endif;?>"><img src="<?php echo $widget_content['icon'];?>" width="16" alt="" /></a>
<?php       else:?>
          <img src="<?php echo $widget_content['icon'];?>" width="16" alt="<?php if (isset($widget_content['icon_title'])):?><?php _h($widget_content['icon_title']);?><?php endif;?>" />
<?php       endif;?>
        </div>
<?php     elseif (!empty($widget_content['thumbnail'])):?>
      <li class="plugg-widget-entry plugg-widget-entry-with-thumbnail">
        <div class="plugg-widget-entry-thumbnail">
<?php       if (!empty($widget_content['thumbnail_link'])):?>
          <a href="<?php echo $widget_content['thumbnail_link'];?>" title="<?php if (isset($widget_content['thumbnail_title'])):?><?php _h($widget_content['thumbnail_title']);?><?php endif;?>"><img src="<?php echo $widget_content['thumbnail'];?>" width="48" alt="" /></a>
<?php       else:?>
          <img src="<?php echo $widget_content['thumbnail'];?>" width="48" alt="<?php if (isset($widget_content['thumbnail_title'])):?><?php _h($widget_content['thumbnail_title']);?><?php endif;?>" />
<?php       endif;?>
        </div>
<?php     else:?>
      <li class="plugg-widget-entry">
<?php     endif;?>
        <div class="plugg-widget-entry-content">
<?php     if (isset($widget_content['title'])):?>
          <div class="plugg-widget-entry-title"><a title="<?php _h($widget_content['title']);?>" href="<?php echo is_array($widget_content['url']) ? $this->Url($widget_content['url']) : h($widget_content['url']);?>"><?php _h(mb_strimlength($widget_content['title'], 0, 100));?></a></div>
<?php     endif;?>
<?php     if (isset($widget_content['body'])):?>
          <p class="plugg-widget-entry-body"><?php _h(mb_strimlength(strip_tags(strtr($widget_content['body'], array("\r" => '', "\n" => ''))), 0, 200));?></p>
<?php     elseif(isset($widget_content['body_html'])):?>
          <div><?php echo $widget_content['body_html'];?></div>
<?php     endif;?>
          <ul class="plugg-menu plugg-widget-entry-meta">
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
  <div class="plugg-widget-links">
<?php if (!empty($widget['links'])):?>
    <ul class="plugg-menu">
<?php   foreach ($widget['links'] as $widget_link):?>
      <li>
        <a href="<?php echo is_array($widget_link['url']) ? $this->Url($widget_link['url']) : h($widget_link['url']);?>"><?php _h($widget_link['title']);?></a>
      </li>
<?php   endforeach;?>
    </ul>
<?php endif;?>
  </div>
</div>
<div class="clear"></div>