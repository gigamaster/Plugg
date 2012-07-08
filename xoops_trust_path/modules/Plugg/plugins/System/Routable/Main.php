<?php
interface Plugg_System_Routable_Main extends Plugg_System_Routable
{
    function systemRoutableGetMainRoutes();
    function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path);
    function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType);
}