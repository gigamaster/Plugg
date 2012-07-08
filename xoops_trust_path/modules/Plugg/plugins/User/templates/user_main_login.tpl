<?php if (!empty($active_auths)):?>
<ul class="plugg-tabs">
  <li<?php if (empty($auth)):?> class="plugg-tab-selected"<?php endif;?>>
    <h3 class="plugg-tab-label"><?php echo $this->LinkToRemote($this->_('Default'), 'plugg-content', array('path' => 'login'), array('path' => ''));?></h3>
  </li>
<?php foreach ($active_auths as $active_auth):?>
  <li<?php if (!empty($auth) && $active_auth->id == $auth->id):?> class="plugg-tab-selected"<?php endif;?>>
    <h3 class="plugg-tab-label"><?php echo $this->LinkToRemote(h($active_auth->title), 'plugg-content', $this->Url('/user/login/' . $active_auth->plugin));?></h3>
  </li>
<?php endforeach;?>
</ul>
<?php endif;?>
<div class="clear"></div>