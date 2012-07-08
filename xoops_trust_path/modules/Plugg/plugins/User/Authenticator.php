<?php
interface Plugg_User_Authenticator
{
    function userAuthGetName();
    function userAuthGetSettings();
    function userAuthGetForm(array $defaultForm);
    function userAuthSubmitForm(Plugg_Form_Form $form);
}