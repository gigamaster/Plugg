<?php
class Plugg_Helper_Trim extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $str)
    {
        if (function_exists('mb_ereg_replace')) {
            $space = $application->_(' '); // get whitespace char for the current locale
            
            return mb_ereg_replace("^($space| |\t|\n|\r|\0|\x0B)*|($space| |\t|\n|\r|\0|\x0B)*$", '', $str);
        }

        return trim($str);
    }
}