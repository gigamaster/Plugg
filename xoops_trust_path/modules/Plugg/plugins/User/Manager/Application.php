<?php
interface Plugg_User_Manager_Application extends Plugg_User_Manager
{
    function userLoginGetForm(array $defaultForm);
    function userLoginSubmitForm(Plugg_Form_Form $form);
    function userLoginUser(Sabai_User $user);
    function userLogoutUser(Sabai_User $user);

    function userRegisterGetForm($username = null, $email = null, $name = null);
    function userRegisterQueueForm(Plugg_User_Model_Queue $queue, Plugg_Form_Form $form);
    function userRegisterSubmit(Plugg_User_Model_Queue $queue);

    function userEditGetForm(Sabai_User_Identity $identity);
    function userEditSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form);

    function userDeleteSubmit(Sabai_User_Identity $identity);

    function userRequestPasswordGetForm(array $defaultForm);
    function userRequestPasswordQueueForm(Plugg_User_Model_Queue $queue, Plugg_Form_Form $form);
    function userRequestPasswordSubmit(Plugg_User_Model_Queue $queue);

    function userEditEmailGetForm(Sabai_User_Identity $identity, array $defaultSettings);
    function userEditEmailQueueForm(Plugg_User_Model_Queue $queue, Sabai_User_Identity $identity, Plugg_Form_Form $form);
    function userEditEmailSubmit(Plugg_User_Model_Queue $queue, Sabai_User_Identity $identity);

    function userEditPasswordGetForm(Sabai_User_Identity $identity, array $defaultSettings);
    function userEditPasswordSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form);

    function userViewRenderIdentity(Sabai_User_Identity $identity, array $extraFields);
}