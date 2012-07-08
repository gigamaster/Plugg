<?php
require_once 'HTML/QuickForm/Rule.php';

class Sabai_HTMLQuickForm_Rule_Uri extends HTML_QuickForm_Rule
{
    /**
     * Validates URI string using the PEAR Validate package
     *
     * @param string $uri
     * @param array $options
     * @return bool
     */
    function validate($uri, $options = null)
    {
        require_once 'Validate.php';
        return Validate::uri($uri, $options);
    }


    function getValidationScript($options = null)
    {
        // Below regex from PEAR Validate package
        $regex = '/^(?:([a-z][-+.a-z0-9]*):)?
              (?:\/\/
              (?:((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();:&=+$,])*)@)?
              (?:((?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[a-z0-9]+)?\.?)
              |([0-9]{1,3}(?:\.[0-9]{1,3}){3}))
              (?::([0-9]*))?)
              ((?:\/(?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'():@&=+$,;])*)*\/?)?
              (?:\?([^#]*))?
              (?:\#((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();\/?:@&=+$,])*))?
              $/i';
        return array("  var regex = " . str_replace(array("\r", "\n", ' '), '', $regex) . ";\n", "{jsVar} != '' && !regex.test({jsVar})");
    }

}