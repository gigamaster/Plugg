<?php
interface Plugg_System_Routable_Admin extends Plugg_System_Routable
{
    function systemRoutableGetAdminRoutes();
    function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path);
    function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType);
}