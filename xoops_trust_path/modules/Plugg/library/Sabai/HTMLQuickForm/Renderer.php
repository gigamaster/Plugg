<?php
require_once 'HTML/QuickForm/Renderer/Default.php';

class Sabai_HTMLQuickForm_Renderer extends HTML_QuickForm_Renderer_Default
{
    private $_groupTemplateDefault,
        $_formClass = array(),
        $_elementClass = array(), $_elementErrrors = array(), $_elementRequired = array(),
        $_elementPrefix = array(), $_elementSuffix = array(), $_elementFieldPrefix = array(), $_elementFieldSuffix = array(),
        $_headerHtml = array(), $_classPrefix = '';
    public $_inGroup = 0;

    function __construct()
    {
        parent::HTML_QuickForm_Renderer_Default();
        $element_template = '
<div class="{class_prefix}form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="{class_prefix}form-field-label"><span>{label}</span><!-- BEGIN required --><span class="{class_prefix}form-field-required">*</span><!-- END required --></div><!-- END label -->
  <div class="{class_prefix}form-field-content">
    <!-- BEGIN error --><div class="{class_prefix}form-field-error"><!-- END error -->
    {element}<!-- BEGIN error --><span class="{class_prefix}form-field-error">{error}</span><!-- END error -->
    <!-- BEGIN error --></div><!-- END error -->
    <!-- BEGIN label_2 --><div class="{class_prefix}form-field-description">{label_2}</div><!-- END label_2 -->
  </div>
</div>
';
        $this->setElementTemplate($element_template);
        $this->setGroupTemplate('
<fieldset class="<!-- BEGIN class -->{class}<!-- END class -->">
  <!-- BEGIN label --><legend><span>{label}</span><!-- BEGIN required --><span class="{class_prefix}form-field-required">*</span><!-- END required --></legend><!-- END label -->
  <div class="{class_prefix}form-field-content">
    <!-- BEGIN label_2 --><div class="{class_prefix}form-field-description">{label_2}</div><!-- END label_2 -->
    <!-- BEGIN error --><div class="{class_prefix}form-field-error"><!-- END error -->
    {element}<!-- BEGIN error --><span class="{class_prefix}form-field-error">{error}</span><!-- END error -->
    <!-- BEGIN error --></div><!-- END error -->
  </div>
</fieldset>');
        $this->setFormTemplate('
<form class="{class_prefix}form {class}"{attributes}>
  <!-- BEGIN header --><div class="{class_prefix}form-header">{header}</div><!-- END header -->
  <div class="{class_prefix}form-fields">{content}{hidden}</div>
</form>');
    }
    
    function setClassPrefix($classPrefix)
    {
        $this->_classPrefix = $classPrefix;
    }

    function renderElement($element, $required, $error)
    {
        if (!$this->_inGroup) {
            $name = $element->getName();
            $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_elementTemplate;
            $this->_html .= $this->_renderElementTemplate($element, $template, $required, $error, true);
        } elseif (!isset($this->_groupElementTemplate[$this->_inGroup])) {
            $name = $element->getName();
            $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_elementTemplate;
            $this->_groupElements[$this->_inGroup][] = $this->_renderElementTemplate($element, $template, $required, $error, true);
        } elseif (!empty($this->_groupElementTemplate[$this->_inGroup])) {
            $this->_groupElements[$this->_inGroup][] = $this->_renderElementTemplate($element, $this->_groupElementTemplate[$this->_inGroup], $required, $error, true);
        } else {
            $this->_groupElements[$this->_inGroup][] = $element->toHtml();
        }
    }

    function _renderElementTemplate($element, $html, $required, $error, $renderElementHtml = false)
    {
        $label = $element->getLabel();
        if (is_array($label)) {
            $nameLabel = array_shift($label);
        } else {
            $nameLabel = $label;
        }

        $element_name = $element->getName();
        if ($nameLabel) {
            $html = str_replace(array('{label}', '<!-- BEGIN label -->', '<!-- END label -->'), array($nameLabel), $html);
            if ($required || !empty($this->_elementRequired[$element_name])) {
                $html = str_replace(array('<!-- BEGIN required -->', '<!-- END required -->'), '', $html);
            }
        }
        if (isset($error) || ($error = @$this->_elementErrors[$element_name])) {
            $html = str_replace(array('{error}', '<!-- BEGIN error -->', '<!-- END error -->'), array(h($error)), $html);
        }
        if (!$element->isFrozen() && is_array($label)) {
            foreach($label as $key => $text) {
                if (empty($text)) continue;
                $key  = is_int($key)? $key + 2: $key;
                $html = str_replace(array("{label_{$key}}", "<!-- BEGIN label_{$key} -->", "<!-- END label_{$key} -->"), array($text), $html);
            }
        }
        if (strpos($html, '{label')) {
            $html = preg_replace(
                array(
                    '/\s*<!-- BEGIN label_(\S+) -->.*<!-- END label_\1 -->\s*/is',
                    '/\s*<!-- BEGIN label -->.*<!-- END label -->\s*/is'
                ),
                '',
                $html
            );
        }

        // Insert class name if any
        if (!empty($this->_elementClass[$element_name])) {
            $html = str_replace(array('{class}', '<!-- BEGIN class -->', '<!-- END class -->'), array(h(implode(' ', $this->_elementClass[$element_name]))), $html);
        }

        // Add field/element level prefix/suffix
        
        if ($renderElementHtml) {
            $field_prefix = $field_suffix = '';
            if (isset($this->_elementFieldPrefix[$element_name])) {
                $field_prefix = '<span class="{class_prefix}form-field-prefix">' . $this->_elementFieldPrefix[$element_name] . '</span>';
            }
            if (isset($this->_elementFieldSuffix[$element_name])) {
                $field_suffix = '<span class="{class_prefix}form-field-suffix">' . $this->_elementFieldSuffix[$element_name] . '</span>';
            }
        
            $html = str_replace('{element}', $field_prefix . $element->toHtml() . $field_suffix, $html);
        }
        
        $prefix = $suffix = '';
        $prefix = isset($this->_elementPrefix[$element_name]) ? $this->_elementPrefix[$element_name] : '';
        $suffix = isset($this->_elementSuffix[$element_name]) ? $this->_elementSuffix[$element_name] : '';

        return implode(PHP_EOL, array($prefix, $html, $suffix));
    }

    function startGroup($group, $required, $error)
    {
        $name = $group->getName();
        ++$this->_inGroup;
        $this->_groupElements[$this->_inGroup] = array();
        $template = isset($this->_templates[$name]) ? $this->_templates[$name] : $this->_groupTemplateDefault;
        $this->_groupTemplate[$this->_inGroup] = $this->_renderElementTemplate($group, $template, $required, $error);
        if (is_callable(array($group, 'getGroupType')) && in_array(@$group->getGroupType(), array('submit', 'radio', 'checkbox', 'button'))) {
            $this->_groupElementTemplate[$this->_inGroup] = '';   
        }
    }

    function finishGroup($group)
    {
        $separator = $group->_separator;
        if (is_null($separator)) $separator = '&nbsp;';
        $html = implode($separator, $this->_groupElements[$this->_inGroup]);
        $html = str_replace('{element}', $html, $this->_groupTemplate[$this->_inGroup]);
        --$this->_inGroup;
        if ($this->_inGroup) {
            $this->_groupElements[$this->_inGroup][] = $html;
        } else {
            $this->_html .= $html;
        }
    }

    function setGroupTemplate($html)
    {
        $this->_groupTemplateDefault = $html;
    }

    function startForm($form)
    {
        parent::startForm($form);
        $this->_headerHtml = '';
    }

    function finishForm($form)
    {
        // add form attributes and content
        $html = str_replace('{attributes}', $form->getAttributes(true), $this->_formTemplate);

        // add header
        if (!empty($this->_headerHtml)) {
            $html = str_replace(array('{header}', '<!-- BEGIN header -->', '<!-- END header -->'), array(implode(PHP_EOL, $this->_headerHtml)), $html);
        }

        if (strpos($this->_formTemplate, '{hidden}')) {
            $html = str_replace('{hidden}', $this->_hiddenHtml, $html);
        } else {
            $this->_html .= $this->_hiddenHtml;
        }
        $this->_hiddenHtml = '';
        $this->_html = str_replace('{content}', $this->_html, $html);

        // remove all remaining comments
        $this->_html = preg_replace(array(
            '/([ \t\n\r]*)?<!-- BEGIN header -->.*<!-- END header -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN class -->.*<!-- END class -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN error -->.*<!-- END error -->([ \t\n\r]*)?/isU',
            '/([ \t\n\r]*)?<!-- BEGIN required -->.*<!-- END required -->([ \t\n\r]*)?/isU',
            '/\s*<!-- BEGIN label(\S*) -->.*<!-- END label\1 -->\s*/is',
        ), '', $this->_html);

        // add a validation script
        if ('' != ($script = $form->getValidationScript())) {
            $this->_html = $script . "\n" . $this->_html;
        }

        // add form classes
        $this->_html = str_replace(
            array('{class_prefix}', '{class}'),
            array($this->_classPrefix, implode(' ', $this->_formClass)),
            $this->_html
        );
    }

    function renderHeader($header)
    {
        $name = $header->getName();
        if (!empty($name) && isset($this->_templates[$name])) {
            $this->_headerHtml[] = str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $this->_headerHtml[] = $header->toHtml();
        }
    }

    function addFormClass($class)
    {
        foreach ((array)$class as $_class) {
            $this->_formClass[$_class] = $_class;
        }
    }
    
    function removeFormClass($class)
    {
        foreach ((array)$class as $_class) {
            unset($this->_formClass[$_class]);
        }
    }

    function addElementClass($elementName, $class)
    {
        foreach ((array)$class as $_class) {
            $this->_elementClass[$elementName][$_class] = $_class;
        }
    }
    
    function removeElementClass($elementName, $class)
    {
        foreach ((array)$class as $_class) {
            unset($this->_elementClass[$elementName][$class]);
        }
    }

    function setElementError($elementName, $message)
    {
        $this->_elementErrors[$elementName] = $message;
    }
    
    function setElementRequired($elementName)
    {
        $this->_elementRequired[$elementName] = true;
    }
    
    function setElementPrefix($elementName, $html)
    {
        $this->_elementPrefix[$elementName] = $html;
    }
    
    function setElementSuffix($elementName, $html)
    {
        $this->_elementSuffix[$elementName] = $html;
    }
    
    function setElementFieldPrefix($elementName, $html)
    {
        $this->_elementFieldPrefix[$elementName] = $html;
    }
    
    function setElementFieldSuffix($elementName, $html)
    {
        $this->_elementFieldSuffix[$elementName] = $html;
    }
}