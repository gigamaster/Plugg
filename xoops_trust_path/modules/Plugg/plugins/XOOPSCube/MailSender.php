<?php
class Plugg_XOOPSCube_MailSender implements Plugg_Mail_Sender
{
    private $_xoopsMailer;

    public function __construct(XoopsMailer $mailer)
    {
        $this->_xoopsMailer = $mailer;
    }

    public function mailSend($to, $subject, $body, array $attachments = null, $bodyHtml = null)
    {
        $to_address = is_array($to) ? $to[0] : $to;
        $headers = array();

        return $this->_xoopsMailer->sendMail($to_address, $subject, $body, $headers);
    }
}