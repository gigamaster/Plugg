<?php
class Plugg_Helper_Split extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $str, $regex = null, $limit = -1)
    {
        if (function_exists('mb_ereg_replace')) {
            if (!isset($regex)) {
                $str = mb_ereg_replace($application->_(' '), ' ', $str);
                $regex = '\s+'; // use space(s) as the separator if regular expression is not specified
            }
            
            return mb_split($regex, $str, $limit);
        }

        return preg_split(isset($regex) ? '/' . $regex . '/' : '/\s+/', $str, $limit);
    }
}