<?php
class Plugg_Helper_Form_FieldsetField extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application)
    {
        return $application->getPlugin('Form')->getFieldsetField();
    }
}