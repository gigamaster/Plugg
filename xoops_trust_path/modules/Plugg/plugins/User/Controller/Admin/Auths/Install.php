<?php
class Plugg_User_Controller_Admin_Auths_Install extends Plugg_System_Controller_InstallPlugin
{
    public function __construct()
    {
        parent::__construct('Plugg_User_Authenticator');
    }
}