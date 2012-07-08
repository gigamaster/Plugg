<?php
interface Plugg_User_Manager_ApplicationWithImage extends Plugg_User_Manager_Application
{
    function userEditImageGetForm(Sabai_User_Identity $identity);
    function userEditImageSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form);
}