<?php
class Plugg_Helper_Form_Fields extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, array $fieldIds)
    {
        return $application->getPlugin('Form')->getModel()->Field->fetchByIds($fieldIds);
    }
}