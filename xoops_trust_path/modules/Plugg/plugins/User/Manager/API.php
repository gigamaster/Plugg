<?php
interface Plugg_User_Manager_API extends Plugg_User_Manager
{
    function userLogin(Sabai_Request $request, Sabai_Application_Response $response, $returnTo);
    function userLogout(Sabai_Request $request, Sabai_Application_Response $response);
    function userView(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userRegister(Sabai_Request $request, Sabai_Application_Response $response);
    function userEdit(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userEditEmail(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userEditPassword(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userEditImage(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userDelete(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity);
    function userRequestPassword(Sabai_Request $request, Sabai_Application_Response $response);
}