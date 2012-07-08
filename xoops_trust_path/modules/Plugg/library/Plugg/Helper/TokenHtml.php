<?php
class Plugg_Helper_TokenHtml extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $tokenId, $tokenLifetime = 1800, $tokenName = Plugg::PARAM_TOKEN)
    {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $tokenName, $application->Token($tokenId, $tokenLifetime));
    }
}