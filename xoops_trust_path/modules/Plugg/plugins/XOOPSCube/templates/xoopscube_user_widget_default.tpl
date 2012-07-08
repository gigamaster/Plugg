<?php foreach (array_keys($module_results) as $module_id): $module = $module_results[$module_id];?>
<h3><?php _h($module['name']);?></h3>
<ul>
<?php   foreach ($module['results'] as $result):?>
  <li>
    <img src="<?php echo $result['image'];?>" alt="<?php _h($result['title']);?>" />
    <a href="<?php echo $result['link'];?>"><?php _h($result['title']);?></a>
    <small>(<?php echo $this->DateTime($result['time']);?>)</small>
  </li>
<?php   endforeach;?>
</ul>
<?php if ($module['has_more'] || count($module['results']) == 5):?><a href="<?php echo $search_url;?>?action=showallbyuser&mid=<?php echo $module_id;?>&uid=<?php echo $identity->id;?>"><?php $this->_e('Show all');?></a><?php endif;?>
<?php endforeach;?>