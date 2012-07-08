<?php
interface Plugg_Form_Field
{
    function formFieldGetFormElementTypes();
    function formFieldGetTitle($type);
    function formFieldGetSummary($type);
    function formFieldGetFormElement($type, $name, array &$data, Plugg_Form_Form $form);
    function formFieldOnSubmitForm($type, $name, &$value, array &$data, Plugg_Form_Form $form);
    function formFieldOnCleanupForm($type, $name, array $data, Plugg_Form_Form $form);
    function formFieldGetSettings($type, array $currentValues);
    function formFieldRenderHtml($type, $value, array $data, array $allValues = array());
}