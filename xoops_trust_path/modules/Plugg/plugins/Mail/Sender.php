<?php
interface Plugg_Mail_Sender
{
    function mailSend($to, $subject, $body, array $attachments = null, $bodyHtml = null);
}