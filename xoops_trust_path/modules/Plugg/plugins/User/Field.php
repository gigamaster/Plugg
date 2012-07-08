<?php
interface Plugg_User_Field
{
    function userFieldGetFormElementTypes();
    function userFieldGetTitle($type);
    function userFieldGetSummary($type);
    function userFieldGetFormElement($type, $name, array &$data, Sabai_HTMLQuickForm $form);
    function userFieldOnSubmitForm($type, $name, &$value, array $data, Plugg_Form_Form $form);
    function userFieldGetSettings($type, array $currentValues);
    function userFieldRenderHtml($type, $value, array $data, array $allValues);
}