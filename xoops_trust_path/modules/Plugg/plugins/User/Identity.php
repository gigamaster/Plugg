<?php
class Plugg_User_Identity extends Sabai_User_Identity
{
    public function __construct($id, $username)
    {
        parent::__construct($id, array(
            'username' => $username,
            'email' => '',
            'url' => '',
            'name' => '',
            'display_name' => $username,
            'created' => 0,
            'image' => '',
            'image_thumbnail' => '',
            'image_icon' => '',
        ));
    }
}