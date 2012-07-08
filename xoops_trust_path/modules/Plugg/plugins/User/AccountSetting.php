<?php
interface Plugg_User_AccountSetting
{
    function userAccountSettingGetNames();
    function userAccountSettingGetSettings($settingName, Plugg_User_Identity $identity);
}