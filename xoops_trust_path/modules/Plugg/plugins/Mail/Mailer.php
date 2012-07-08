<?php
interface Plugg_Mail_Mailer
{
    function mailGetNicename();
    function mailGetSender();
    function mailGetSettings();
}