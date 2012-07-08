<?php
class Plugg_XOOPSCubeUser_EditFormBuilder extends Plugg_XOOPSCubeUser_FormBuilder
{
    protected function _isFieldEnabled($value)
    {
        return in_array(Plugg_XOOPSCubeUser_Plugin::FIELD_EDITABLE, (array)$value);
    }
}