<ul>
  <li>
    <?php printf($this->_('<a href="%1$s">Your profile</a> (<a href="%2$s">Edit</a>)'), $this->Url(array('base' => '/user')), $this->Url(array('base' => '/user/edit')));?>
  </li>
  <li>
    <a href="<?php echo $this->Url(array('base' => '/user/settings'));?>"><?php $this->_e('Account settings');?></a>
  </li>
  <li>
    <a href="<?php echo $this->Url(array('base' => '/user/logout'));?>"><?php $this->_e('Logout');?></a>
  </li>
<?php if ($this->User()->isSuperUser()):?>
  <li>
    <a href="<?php echo $this->Url(array('script' => 'admin', 'base' => '/'));?>"><?php $this->_e('Administration');?></a>
  </li>
<?php endif;?>
</ul>
<?php if (!empty($menus)):?>
<ul>
<?php   foreach ($menus as $menu):?>
<?php     if ($menu):?>
  <li>
    <a href="<?php echo $menu['url'];?>"><?php echo $menu['text'];?></a>
  </li>
<?php     endif;?>
<?php   endforeach;?>
</ul>
<?php endif;?>