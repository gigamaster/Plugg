<?php
class Plugg_Helper_Token extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $tokenId, $tokenLifetime = 1800)
    {
        return Sabai_Token::create($tokenId, $tokenLifetime)->getValue();
    }
}