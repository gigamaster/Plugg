<?php
class Plugg_Helper_User extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application)
    {
        return $application->getUser();
    }
}