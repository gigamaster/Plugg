<?php
class Plugg_User_AnonymousIdentity extends Sabai_User_AnonymousIdentity
{
    public function __construct($name)
    {
        parent::__construct(array(
            'username' => '',
            'display_name' => $name,
            'email' => '',
            'url' => '',
            'name' => $name,
            'image' => '',
            'created' => 0,
            'image' => '',
            'image_thumbnail' => '',
            'image_icon' => '',
        ));
    }
}