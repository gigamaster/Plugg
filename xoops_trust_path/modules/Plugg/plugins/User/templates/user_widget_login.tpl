<ul>
  <li>
    <a title="<?php $this->_e('Login here if you already have an account.');?>" href="<?php echo $this->Url(array('base' => '/user/login', 'params' => array('return' => 1)));?>"><?php $this->_e('Login');?></a>
  </li>
  <li>
    <a title="<?php $this->_e('Forgotten password? Request new password here.');?>" href="<?php echo $this->Url(array('base' => '/user/request_password'));?>"><?php $this->_e('Request password');?></a>
  </li>
  <li>
    <a title="<?php $this->_e('New to this website? Register here now!');?>" href="<?php echo $this->Url(array('base' => '/user/register'));?>"><?php $this->_e('Create account');?></a>
  </li>
</ul>