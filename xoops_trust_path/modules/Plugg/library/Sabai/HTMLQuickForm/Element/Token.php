<?php
require_once 'Sabai/Token.php';
require_once 'HTML/QuickForm/hidden.php';

class Sabai_HTMLQuickForm_Element_Token extends HTML_QuickForm_hidden
{
    var $_tokenId;
    var $_tokenLifetime;

    function __construct($elementName = null, $tokenId = null, $tokenLifetime = null, $attributes = null)
    {
        parent::HTML_QuickForm_hidden($elementName, '', $attributes);
        $this->_tokenId = $tokenId;
        $this->_tokenLifetime = $tokenLifetime;
    }

    function Sabai_HTMLQuickForm_Element_Token($elementName = null, $tokenId = null, $tokenLifetime = null, $attributes = null)
    {
        $this->__construct($elementName, $tokenId, $tokenLifetime, $attributes);
    }

    function getTokenId()
    {
        return $this->_tokenId;
    }

    function accept($renderer)
    {
        $this->setValue(Sabai_Token::create($this->_tokenId, $this->_tokenLifetime)->getValue());
        parent::accept($renderer);
    }
}