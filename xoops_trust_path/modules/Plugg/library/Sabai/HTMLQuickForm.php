<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Form
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/altselect.php';
require_once 'HTML/QuickForm/ElementGrid.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Form
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_HTMLQuickForm extends HTML_QuickForm
{
    var $_formRuleParams;
    var $_renderer;

    function Sabai_HTMLQuickForm($formName = '', $method = 'post', $action = '', $target = '', $attributes = null, $trackSubmit = true, array $submitValues = null)
    {
        if (empty($action)) $action = isset($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : $_SERVER['REQUEST_URI'];

        // Mostly Copy & Paste from HTML_QuickForm constructor without the magic quotes part
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $target = empty($target) ? array() : array('target' => $target);
        $attributes = array('action' => $action, 'method' => $method, 'name' => $formName, 'id' => $formName) + $target;
        $this->updateAttributes($attributes);
        $track_submit_ele_name = $this->getTrackSubmitElementName();
        if (!$trackSubmit || isset($_REQUEST[$track_submit_ele_name])) {
            $this->_submitValues = isset($submitValues) ? $submitValues : ('get' == $method ? $_GET: $_POST);
            $this->_submitFiles  = $_FILES;
            $this->_flagSubmitted = count($this->_submitValues) > 0 || count($this->_submitFiles) > 0;
        }
        if ($trackSubmit) {
            //unset($this->_submitValues[$track_submit_ele_name]);
            $this->addElement('hidden', $track_submit_ele_name, null);
        }

        if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
            // see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
            switch (strtoupper($matches['2'])) {
                case 'G':
                    $this->_maxFileSize = $matches['1'] * 1073741824;
                    break;
                case 'M':
                    $this->_maxFileSize = $matches['1'] * 1048576;
                    break;
                case 'K':
                    $this->_maxFileSize = $matches['1'] * 1024;
                    break;
                default:
                    $this->_maxFileSize = $matches['1'];
                    break;
            }
        }

        $this->setJsWarnings('', '');
    }

    function init()
    {
        static $initialized = false;
        // Initialize only once since these methods just add entries to global/static variables
        if (!$initialized) {
            // HTML_Common uses charset defined here for htmlspecialchars
            HTML_Common::charset(SABAI_CHARSET);

            self::registerElementType('token', 'Sabai/HTMLQuickForm/Element/Token.php', 'Sabai_HTMLQuickForm_Element_Token');
            self::registerElementType('selectmodelentity', 'Sabai/HTMLQuickForm/Element/SelectModelEntity.php', 'Sabai_HTMLQuickForm_Element_SelectModelEntity');
            self::registerElementType('selectmodeltreeentity', 'Sabai/HTMLQuickForm/Element/SelectModelTreeEntity.php', 'Sabai_HTMLQuickForm_Element_SelectModelTreeEntity');
            self::registerElementType('file', 'Sabai/HTMLQuickForm/Element/File.php', 'Sabai_HTMLQuickForm_Element_File');
            self::registerElementType('group', 'Sabai/HTMLQuickForm/Element/Group.php', 'Sabai_HTMLQuickForm_Element_Group');
            self::registerElementType('checkbox', 'Sabai/HTMLQuickForm/Element/Checkbox.php', 'Sabai_HTMLQuickForm_Element_Checkbox');
            self::registerElementType('textmulti', 'Sabai/HTMLQuickForm/Element/TextMulti.php', 'Sabai_HTMLQuickForm_Element_TextMulti');
            self::registerElementType('grid', 'Sabai/HTMLQuickForm/Element/Grid.php', 'Sabai_HTMLQuickForm_Element_Grid');
            // Use custom callback/required rules instead of the default ones which are really old
            self::registerRule('callback', null, 'Sabai_HTMLQuickForm_Rule_Callback', 'Sabai/HTMLQuickForm/Rule/Callback.php');
            self::registerRule('required', null, 'Sabai_HTMLQuickForm_Rule_Required', 'Sabai/HTMLQuickForm/Rule/Required.php');
            // Add some custom rules
            self::registerRule('inclusion', null, 'Sabai_HTMLQuickForm_Rule_Inclusion', 'Sabai/HTMLQuickForm/Rule/Inclusion.php');
            self::registerRule('exclusion', null, 'Sabai_HTMLQuickForm_Rule_Exclusion', 'Sabai/HTMLQuickForm/Rule/Exclusion.php');
            self::registerRule('token', 'callback', 'validate', 'Sabai_Token');
            self::registerRule('uri', null, 'Sabai_HTMLQuickForm_Rule_Uri', 'Sabai/HTMLQuickForm/Rule/Uri.php');

            $initialized = true;
        }
    }

    function getTrackSubmitElementName()
    {
        return '_qf__' . $this->getAttribute('name');
    }

    function useToken($tokenId = null, $tokenElementName = '', $allowMultiple = false, $tokenLifetime = 1800)
    {
        $token_element_name = ($tokenElementName == '') ? '__T' : $tokenElementName;
        if ($this->elementExists($token_element_name)) {
            if ('token' == $this->getElementType($token_element_name)) {
                $token = $this->getElement($token_element_name);
                return $token->getTokenId();
            }
            $token_element_name .= '_';
        }
        $token_id = empty($tokenId) ? strtolower(get_class($this)) : $tokenId;
        $this->addElement('token', $token_element_name, $token_id, $tokenLifetime);
        $this->addRule($token_element_name, 'Invalid token', 'token', array($token_id, !$allowMultiple));

        return $token_id;
    }

    function setElementValue($elementName, $value)
    {
        $this->getElement($elementName)->setValue($value);
    }

    function setRequired($elementName, $errorMessage, $trim = true, $charlist = '')
    {
        if ($this->getElementType($elementName) == 'file') {
            $this->addRule($elementName, $errorMessage, 'uploadedfile', @$_FILES[$elementName]);
            return;
        }

        if ($trim) {
            $this->addRule($elementName, $errorMessage, 'required', " \t\n\r\0\x0B" . $charlist);
        } else {
            $this->addRule($elementName, $errorMessage, 'required');
        }
    }

    function setCallback($elementName, $errorMessage, $callback, $params = array())
    {
        if (empty($params)) {
            $this->addRule($elementName, $errorMessage, 'callback', $callback);
        } else {
            if (is_array($callback)) {
                $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
                $name = strtolower($class . '::' . $callback[1]);
            } else {
                $name = strtolower($callback);
            }
            $this->registerRule($name, 'callback', $callback);
            $this->addRule($elementName, $errorMessage, $name, $params);
        }
    }

    function insertElementAfter($element, $nameBefore)
    {
        if (!empty($this->_duplicateIndex[$nameBefore])) {
            $error = PEAR::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, 'Several elements named "' . $nameBefore . '" exist in HTML_QuickForm::insertElementBefore().', 'HTML_QuickForm_Error', true);
            return $error;
        } elseif (!$this->elementExists($nameBefore)) {
            $error = PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$nameBefore' does not exist in HTML_QuickForm::insertElementBefore()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $last_index = end(array_keys($this->_elements));
        for ($i = $this->_elementIndex[$nameBefore] + 1; $i <= $last_index; $i++) {
            if (isset($this->_elements[$i])) {
                return $this->insertElementBefore($element, $this->_elements[$i]->getName());
            }
        }
        return $this->addElement($element);
    }

    function hideElement($elementName)
    {
        $element = $this->getElement($elementName);
        switch ($element->getType()) {
            case 'hidden':
            case 'submit':
                return;
            case 'group':
                // Do not hide submit buttons
                if ($element->getGroupType() == 'submit') return;

                $element = $this->removeElement($elementName);
                $elements = $element->getElements();
                foreach (array_keys($elements) as $i) {
                    if ($name = $elements[$i]->getName()) {
                        $value = $elements[$i]->getValue();
                        if (is_array($value)) {
                            foreach ($value as $_value) {
                                $this->addElement('hidden', $name, (string)$value);
                            }
                        } else {
                            $this->addElement('hidden', $name, (string)$value);
                        }
                    }
                }
                return $element;
            default:
                $element = $this->removeElement($elementName);
                if ($name = $element->getName()) {
                    $value = $element->getValue();
                    if (is_array($value)) {
                        foreach ($value as $_value) {
                            $this->addElement('hidden', $name, (string)$_value);
                        }
                    } else {
                        $this->addElement('hidden', $name, (string)$value);
                    }
                }
                return $element;
        }
    }

    /**
     * Overrides the parent to allow checking if element exists in a group as well
     *
     * @param string $elementName
     * @param bool $searchGroups
     * @return bool
     */
    function elementExists($elementName, $searchGroups = false)
    {
        if (parent::elementExists($elementName)) {
            return true;
        }
        if ($searchGroups) {
            return false !== $this->isInGroup($elementName);
        }
        return false;
    }

    function isInGroup($elementName, $groupElementName = '')
    {
        if ($groupElementName != '') {
            if (!$this->elementExists($groupElementName)) return false;

            $group_element = $this->getElement($groupElementName);
            if ($group_element->getType() != 'group') return false;

            return $group_element->getElement($elementName) ? $groupElementName : false;
        }

        foreach (array_keys($this->_elements) as $i) {
            if ($this->_elements[$i]->getType() == 'group') {
                if ($this->_elements[$i]->getElement($elementName)) {
                    return $this->_elements[$i]->getName();
                }
            }
        }

        return false;
    }

    function setElementLabel($elementName, $elementLabel, $labelIndex = null)
    {
        if (!isset($labelIndex)) {
            $this->getElement($elementName)->setLabel($elementLabel);
            return;
        }

        $label = $this->getElementLabel($elementName);
        $label[$labelIndex] = $elementLabel;
        $this->setElementLabel($elementName, $label);
    }

    function getElementLabel($elementName, $labelIndex = null)
    {
        if ($this->elementExists($elementName)) {
            $label = (array)$this->getElement($elementName)->getLabel();
        } else {
            if (!$group_name = $this->isInGroup($elementName)) {
                return;
            }
            // Use group element label
            $label = $this->getElement($group_name)->getLabel();
        }
        if (isset($labelIndex) && isset($label[$labelIndex])) {
            return $label[$labelIndex];
        }
        return $label;
    }

    function removeElements($elementNames, $removeRules = true)
    {
        $ret = array();
        foreach ((array)$elementNames as $element_name) {
            $ret[$element_name] = $this->removeElement($element_name, $removeRules);
        }
        return $ret;
    }

    function removeElementsExcept($elementNames, $removeRules = true)
    {
        $ret = array();
        settype($elementNames, 'array');
        foreach (array_keys($this->_elements) as $i) {
            $element_name = $this->_elements[$i]->getName();
            if (!in_array($element_name, $elementNames)) {
                $ret[] = $this->removeElement($element_name, $removeRules);
            }
        }
        return $ret;
    }

    function removeElementsAll($removeRules = true)
    {
        return $this->removeElementsExcept(array(), $removeRules);
    }

    function removeGroupedElements($elementNames, $groupName)
    {
        if ($this->elementExists($groupName) &&
            ($group = $this->getElement($groupName))
        ) {
            if ($elements_in_group = $group->getElements()) {
                foreach (array_keys($elements_in_group) as $i) {
                    foreach ($elementNames as $element_name) {
                        if ($elements_in_group[$i]->getName() == $element_name) {
                            unset($this->_rules[$element_name]);
                            $elements_found[] = $element_name;

                            continue 2;
                        }
                    }
                    $elements[] = $elements_in_group[$i];
                }
                if (!empty($elements)) {
                    $group->setElements($elements);
                    if (!empty($elements_found)) {
                        $this->_required = array_diff($this->_required, $elements_found);
                        $group->_required = array_diff($group->_required, $elements_found);
                    }
                } else {
                    $this->removeElement($groupName, true);
                }
            }
        }
    }

    function groupElements($elements, $name, $groupLabel = '', $separator = '')
    {
        $ret = false;
        foreach (array_keys($elements) as $i) {
            $element_name = is_object($elements[$i]) ? $elements[$i]->getName() : $elements[$i];
            if ($this->elementExists($element_name)) {
                $group[] = $this->removeElement($element_name, true);
            }
        }
        if (!empty($group)) {
            $ret = $this->createElement('group', $name, $groupLabel, $group, $separator);
        }
        return $ret;
    }

    function appendElement($element, $groupName = null)
    {
        if (!is_null($groupName) &&
            $this->elementExists($groupName) &&
            ($group = $this->getElement($groupName))
        ) {
            $elements = $group->getElements();
            $elements[] = $element;
            $group->setElements($elements);
            return $element;
        }

        // Append element to the form if no group
        return $this->addElement($element);
    }

    function prependElement($element, $groupName = null)
    {
        if (!is_null($groupName) &&
            $this->elementExists($groupName) &&
            ($group = $this->getElement($groupName))
        ) {
            $elements = $group->getElements();
            array_unshift($elements, $element);
            $group->setElements($elements);
            return $element;
        }

        // Prepend element to the form if no group
        $first_index = current(array_keys($this->_elements));
        return $this->insertElementBefore($element, $this->_elements[$first_index]->getName());
    }

    function defaultRenderer()
    {
        return $this->getRenderer();
    }

    function getRenderer()
    {
        if (!isset($this->_renderer)) {
            require_once 'Sabai/HTMLQuickForm/Renderer.php';
            $this->_renderer = new Sabai_HTMLQuickForm_Renderer();
        }
        
        return $this->_renderer;
    }

    function renderElements($renderer = null)
    {
        if (!isset($renderer)) $renderer = $this->getRenderer();
        $renderer->setFormTemplate('{content}{hidden}');

        return $this->_render($renderer);
    }

    function render($renderer = null)
    {
        if (!isset($renderer)) $renderer = $this->getRenderer();
        return $this->_render($renderer);
    }

    protected function _render($renderer)
    {
        $this->accept($renderer);

        return $renderer->toHtml();
    }

    function createStatic($text, $name = null, $label = null)
    {
        return $this->createElement('static', $name, $label, $text);
    }

    function removeExtraLabels($elementName)
    {
        $element = $this->getElement($elementName);
        if (($label = $element->getLabel()) && is_array($label)) {
            $element->setLabel(array_shift($label));
        }
    }

    /*
     * Overrides the parent method to allow setting an error message for each element
     * inside a group element
     * http://pear.php.net/bugs/bug.php?id=14997
     */
    function validate()
    {
        if (count($this->_rules) == 0 && count($this->_formRules) == 0 &&
            $this->isSubmitted()) {
            return (0 == count($this->_errors));
        } elseif (!$this->isSubmitted()) {
            return false;
        }
        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry = HTML_QuickForm_RuleRegistry::singleton();
        foreach ($this->_rules as $target => $rules) {
            $submitValue = $this->getSubmitValue($target);
            foreach ($rules as $rule) {
                if ((isset($rule['group']) && isset($this->_errors[$rule['group']][$target])) ||
                     isset($this->_errors[$target])) {
                    continue 2;
                }
                // If element is not required and is empty, we shouldn't validate it
                if (!$this->isElementRequired($target)) {
                    if (!isset($submitValue) || '' == $submitValue) {
                        continue 2;
                    // Fix for bug #3501: we shouldn't validate not uploaded files, either.
                    // Unfortunately, we can't just use $element->isUploadedFile() since
                    // the element in question can be buried in group. Thus this hack.
                    // See also bug #12014, we should only consider a file that has
                    // status UPLOAD_ERR_NO_FILE as not uploaded, in all other cases
                    // validation should be performed, so that e.g. 'maxfilesize' rule
                    // will display an error if status is UPLOAD_ERR_INI_SIZE
                    // or UPLOAD_ERR_FORM_SIZE
                    } elseif (is_array($submitValue)) {
                        if (false === ($pos = strpos($target, '['))) {
                            $isUpload = !empty($this->_submitFiles[$target]);
                        } else {
                            $base = str_replace(
                                        array('\\', '\''), array('\\\\', '\\\''),
                                        substr($target, 0, $pos)
                                    );
                            $idx  = "['" . str_replace(
                                        array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"),
                                        substr($target, $pos + 1, -1)
                                    ) . "']";
                            eval("\$isUpload = isset(\$this->_submitFiles['{$base}']['name']{$idx});");
                        }
                        if ($isUpload && (!isset($submitValue['error']) || UPLOAD_ERR_NO_FILE == $submitValue['error'])) {
                            continue 2;
                        }
                    }
                }
                if (isset($rule['dependent']) && is_array($rule['dependent'])) {
                    $values = array($submitValue);
                    foreach ($rule['dependent'] as $elName) {
                        $values[] = $this->getSubmitValue($elName);
                    }
                    $result = $registry->validate($rule['type'], $values, $rule['format'], true);
                } elseif (is_array($submitValue) && !isset($rule['howmany'])) {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], true);
                } else {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], false);
                }
                if (!$result || (!empty($rule['howmany']) && $rule['howmany'] > (int)$result)) {
                    if (isset($rule['group'])) {
                        $this->_errors[$rule['group']][$target] = $rule['message'];
                    } else {
                        $this->_errors[$target] = $rule['message'];
                    }
                }
            }
        }
        // process the global rules now
        //foreach ($this->_formRules as $rule) {
            //if (true !== ($res = call_user_func($rule, $this->_submitValues, $this->_submitFiles))) {

        // Allow passing form rule parameters, by onokazu
        foreach (array_keys($this->_formRules) as $i) {
            if (true !== $res = call_user_func_array($this->_formRules[$i], array_merge(array($this->_submitValues, $this->_submitFiles), $this->_formRuleParams[$i]))) {
        // onokazu
                if (is_array($res)) {
                    $this->_errors += $res;
                } else {
                    //return PEAR::raiseError(null, QUICKFORM_ERROR, null, E_USER_WARNING, 'Form rule callback returned invalid value in HTML_QuickForm::validate()', 'HTML_QuickForm_Error', true);
                    // allow returning other than array
                    $has_error = true;
                }
            }
        }
        return (0 == count($this->_errors) && empty($has_error));
    }

    // Allow passing form rule paramters
    function addFormRule($rule, $params = array())
    {
        parent::addFormRule($rule);
        $this->_formRuleParams[] = $params;
    }

    function addHeader($text, $elementName = null)
    {
        return $this->addElement('header', $elementName, $text);
    }

    function getValidationScript() {
        return ''; // disable client side validation
    }
}

// This must be called to register required element types and rules
Sabai_HTMLQuickForm::init();