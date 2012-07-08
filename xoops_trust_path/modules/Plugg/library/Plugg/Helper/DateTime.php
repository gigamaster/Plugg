<?php
class Plugg_Helper_DateTime extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $time)
    {
        $diff = $time - time();
        if ($ago = (0 >= $diff)) $diff = abs($diff);

        // Use the core message catalogue
        $gettext = $application->getGettext();

        if ($diff >= 172800) return $this->_daysAgo($gettext, $ago, $diff / 86400, $diff % 86400);

        if ($diff >= 86400) return $this->_dayAgo($gettext, $ago, $diff % 86400);

        if ($diff >= 7200) return $this->_hoursAgo($gettext, $ago, $diff / 3600, $diff % 3600);

        if ($diff >= 3600) return $this->_hourAgo($gettext, $ago, $diff % 3600);

        if ($diff >= 120) return $this->_minutesAgo($gettext, $ago, $diff / 60);

        return $ago ? $gettext->_('1 minute ago') : $gettext->_('1 minute');
    }

    private function _daysAgo($gettext, $ago, $days, $time)
    {
        if (!$time = intval($time / 3600)) $time = 1;

        return $ago
            ? sprintf($gettext->ngettext('%d days ago', '%d days %d hours ago', $time), $days, $time)
            : sprintf($gettext->ngettext('%d days', '%d days %d hours', $time), $days, $time);
    }

    private function _dayAgo($gettext, $ago, $time)
    {
        if (!$time = intval($time / 3600)) $time = 1;

        return $ago
            ? sprintf($gettext->ngettext('1 day ago', '1 day %d hours ago', $time), $time)
            : sprintf($gettext->ngettext('1 day', '1 day %d hours', $time), $time);
    }

    private function _hoursAgo($gettext, $ago, $hours, $time)
    {
        if (!$time = intval($time / 60)) $time = 1;

        return $ago
            ? sprintf($gettext->ngettext('%d hours ago', '%d hours %d min ago', $time), $hours, $time)
            : sprintf($gettext->ngettext('%d hours', '%d hours %d min', $time), $hours, $time);
    }

    private function _hourAgo($gettext, $ago, $time)
    {
        if (!$time = intval($time / 60)) $time = 1;

        return $ago
            ? sprintf($gettext->ngettext('1 hour ago', '1 hour %d min ago', $time), $time)
            : sprintf($gettext->ngettext('1 hour', '1 hour %d min', $time), $time);
    }

    private function _minutesAgo($gettext, $ago, $minutes)
    {
        return $ago
            ? sprintf($gettext->_('%d minutes ago'), $minutes)
            : sprintf($gettext->_('%d minutes'), $minutes);
    }
}