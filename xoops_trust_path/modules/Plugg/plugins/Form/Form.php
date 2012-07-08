<?php
class Plugg_Form_Form
{
    public $settings, $values, $storage, $rebuild = false;
    protected $_plugin, $_htmlquickform, $_elements, $_errors = array(), $_clickedButton = null,
        $_submitted = false, $_defaultElementType = 'textfield';
    protected static $_defaultElementSettings = array(
        '#type' => null,
        '#title' => '',
        '#description' => null,
        '#value' => null,
        '#attributes' => array(),
        '#weight' => 0,
        '#element_validate' => null,
        '#required' => null,
        '#disabled' => null,
        '#tree' => null,
        '#tree_allow_override' => true,
        '#class' => null,
        '#template' => null,
        '#style' => null,
        '#prefix' => null,
        '#suffix' => null,
        '#collapsible' => false,
        '#collapsed' => false,
        '#children' => array(), // for internal use only by the fieldset field
        '#processed' => false,
    );

    public function __construct(Plugg_Plugin $plugin, array $settings, array $storage)
    {
        $this->_plugin = $plugin;
        $this->settings = $settings;
        $this->storage = $storage;
    }
    
    public function build(array $values = null)
    {
        $this->_htmlquickform = new Sabai_HTMLQuickForm(
            isset($this->settings['#id']) ? $this->settings['#id'] : '',
            !empty($this->settings['#method']) ? $this->settings['#method'] : 'post',
            !empty($this->settings['#action']) ? $this->settings['#action'] : '',
            !empty($this->settings['#target']) ? $this->settings['#target'] : '',
            !empty($this->settings['#attributes']) ? $this->settings['#attributes'] : null,
            isset($this->settings['#trackSubmit']) && false === $this->settings['#trackSubmit'] ? false : true,
            $values
        );

        // Add form headers if any
        if (!empty($this->settings['#header'])) {
            foreach ($this->settings['#header'] as $header) $this->_htmlquickform->addHeader($header);
        }

        if (isset($this->settings['#id']) && !empty($this->settings['#token'])) {
            if (!is_array($this->settings['#token'])) {
                $this->_htmlquickform->useToken($this->settings['#id']);
            } else {
                $form_token = $this->settings['#token'];
                $token_id = empty($form_token['id']) ? $this->settings['#id'] : $form_token['id'];
                $token_name = empty($form_token['name']) ? Plugg::PARAM_TOKEN : $form_token['name'];
                $token_lifetime = empty($form_token['lifetime']) ? 1800 : $form_token['lifetime'];
                $this->_htmlquickform->useToken($token_id, $token_name, !empty($form_token['reuse']), $token_lifetime);
            }
        }

        // Add form elements
        $this->_elements = array();
        $this->_extractAndSortElementSettings($this->settings, $this->_elements, !empty($this->settings['#ignore_default_value']), null);
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_key) {
                $this->_htmlquickform->addElement(
                    $this->createElement(
                        $this->_elements['#children'][$weight][$ele_key]['#type'],
                        $ele_key,
                        $this->_elements['#children'][$weight][$ele_key]
                    )
                );
            }
        }
    }
    
    public function createElement($type, $key, &$data)
    {
        if (empty($type)) $type = $this->_defaultElementType;
        if (empty($data['#type'])) $data['#type'] = $type;
        
        // Convert the label data into array and add description/tip data there since
        // the HTML_QuickForm library only allows label data to be added as 1 variable.
        if (empty($data['#label'])) $data['#label'] = array(h($data['#title']), $data['#description']);
        
        // Merge #id #disabled, and #style setting with #attributes
        if (isset($data['#id'])) $data['#attributes']['id'] = $data['#id'];
        if (!empty($data['#disabled'])) $data['#attributes']['disabled'] = 'disabled';
        if (isset($data['#style'])) {            
            $styles = array();
            foreach ($data['#style'] as $k => $v) $styles[] = "$k:$v";
            $data['#attributes']['style'] = implode(';', $styles);
        }

        // Get the element
        $element = $this->_plugin->getElementHandler($type)->formFieldGetFormElement($type, $key, $data, $this);
        
        // Has this element already been processed?
        if ($data['#processed']) return $element;
        
        $data['#name'] = $element->getName();

        // Set default value
        if (isset($data['#default_value'])) {
            $this->_htmlquickform->setDefaults(array($data['#name'] => $data['#default_value']));
        }
        // Set constant value that cannot be modified by the user
        if (isset($data['#value'])) $this->_htmlquickform->setConstants(array($data['#name'] => $data['#value']));

        // Fetch renderer to set some display options
        $renderer = $this->_htmlquickform->getRenderer();
        
        // Make it collapsible?
        if ($data['#collapsible'] && strlen($data['#label'][0])) {
            if ($data['#collapsed']) {
                $renderer->addElementClass($data['#name'], array('plugg-collapsible', 'plugg-collapsed'));
            } else {
                $renderer->addElementClass($data['#name'], 'plugg-collapsible');
            }
        }

        // Add a special css class if no label
        if (strlen($data['#label'][0]) === 0) $renderer->addElementClass($data['#name'], 'plugg-nolabel');
        
        if (!empty($data['#class'])) $renderer->addElementClass($data['#name'], $data['#class']);
        
        if ($data['#required']) {
            $renderer->setElementRequired($data['#name']);
            
            if ($data['#disabled']) $data['#required'] = false; // skip required validation, but show as required
        }
        
        // Element prefix/suffix
        if (isset($data['#prefix'])) $renderer->setElementPrefix($data['#name'], $data['#prefix']);
        if (isset($data['#suffix'])) $renderer->setElementSuffix($data['#name'], $data['#suffix']);
        if (isset($data['#field_prefix'])) $renderer->setElementFieldPrefix($data['#name'], $data['#field_prefix']);
        if (isset($data['#field_suffix'])) $renderer->setElementFieldSuffix($data['#name'], $data['#field_suffix']);
        
        // Template may be an empty sting, so use isset() here.
        if (isset($data['#template'])) {
            if (false === $data['#template'] || $data['#template'] === '') {
                $renderer->setElementTemplate('{element}', $data['#name']);
            } else {
                $renderer->setElementTemplate($data['#template'], $data['#name']);
            }
        }
        
        $data['#processed'] = true; // mark as processed

        return $element;
    }
    
    public function doCreateElement()
    {
        $args = func_get_args();
        
        // Create an HTML_QuickForm element
        return call_user_func_array(array($this->_htmlquickform, 'createElement'), $args);   
    }

    private function _extractAndSortElementSettings(array &$settings, array &$elements, $ignoreDefaultValue, $parent = null)
    {
        foreach (array_keys($settings) as $key) {
            if (0 === strpos($key, '#')) continue;

            $settings[$key] = $settings[$key] + self::$_defaultElementSettings;
            if ($ignoreDefaultValue) $settings[$key]['#default_value'] = null;
            
            $weight = intval($settings[$key]['#weight']);
            $elements['#children'][$weight][$key] = $settings[$key];

            $this->_extractAndSortElementSettings($settings[$key], $elements['#children'][$weight][$key], $ignoreDefaultValue, $key);

            if (isset($parent)) {
                if (empty($elements['#type'])) $elements['#type'] = 'fieldset';
                unset($elements[$key]); // remove redundant element data
            }
        }

        // Sort elements by the #weight setting
        ksort($elements['#children']);
    }
    
    public static function defaultElementSettings()
    {
        return self::$_defaultElementSettings;
    }
    
    public function setDefaultValues(array $values)
    {
        $this->_htmlquickform->setDefaults($values);
    }
    
    public function setConstantValues(array $values)
    {
        $this->_htmlquickform->setConstants($values);
    }

    public function setError($message, $elementName = '')
    { 
        if (is_array($elementName)) {
            foreach ($elementName as $element_name) {
                $this->_errors[$element_name][] = $message;
            }
        } else {
            $this->_errors[$elementName][] = $message;
        }
    }

    public function hasError($elementName = null)
    {
        return isset($elementName) ? !empty($this->_errors[$elementName]) : !empty($this->_errors);
    }
    
    public function getClickedButton($property = null)
    {
        return isset($property) ? $this->_clickedButton[$property] : $this->_clickedButton;
    }
    
    public function setClickedButton(array $elementData)
    {
        $this->_clickedButton = $elementData;
    }
    
    public function hasClickedButton()
    {
        return isset($this->_clickedButton);
    }
    
    public function setSubmitted($flag = true)
    {
        $this->_submitted = $flag;
    }
    
    public function isSubmitted()
    {
        return $this->_submitted;
    }
    
    public function submit()
    {
        if (!$this->_htmlquickform->validate()) return false;
        
        $this->values = $this->_htmlquickform->getSubmitValues(false);
        //$this->values = $this->_htmlquickform->exportValues();

        // Allow each field to work on its submitted value before being processed by the submit callbacks
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_name) {
                $ele_data =& $this->_elements['#children'][$weight][$ele_name];
                $ele_type = $ele_data['#type'];
                if (!isset($this->values[$ele_name])) {
                    if ($ele_type == 'submit') continue; // the button was not clicked
 
                    $this->values[$ele_name] = null;
                }

                // Process element level system validations       
                if (false === $this->_plugin->getElementHandler($ele_type)->formFieldOnSubmitForm($ele_type, $ele_name, $this->values[$ele_name], $ele_data, $this)
                    || !empty($this->_errors)
                ) continue;

                // Process element level custom validations if any
                if (!empty($ele_data['#element_validate'])) {
                    foreach ($ele_data['#element_validate'] as $callback) {
                        if (false === Plugg::processCallback($callback, array($this, &$this->values[$ele_name], $ele_name))
                            || !empty($this->_errors)
                        ) continue 2;
                    }
                }
                
                if ($ele_type == 'submit') {
                    // Save as clicked button
                    $this->_clickedButton = $ele_data;
                    
                    // Append global validate/submit handlers if any set for this button
                    if (!empty($ele_data['#validate'])) {
                        foreach ($ele_data['#validate'] as $callback) {
                            $this->settings['#validate'][] = $callback;
                        }
                    }
                    if (!empty($ele_data['#submit'])) {
                        foreach ($ele_data['#submit'] as $callback) {
                            $this->settings['#submit'][] = $callback;
                        }
                    }
                }
            }
        }

        return empty($this->_errors);
    }
    
    public function cleanup()
    {
        foreach ($this->_elements['#children'] as $weight => $_elements) {
            foreach ($_elements as $ele_name => $ele_data) {
                $ele_type = $ele_data['#type'];
                // Process cleanup      
                $this->_plugin->getElementHandler($ele_type)->formFieldOnCleanupForm($ele_type, $ele_name, $ele_data, $this);
            }
        }
    }

    public function render($elementsOnly = false, $freeze = false)
    {
        $renderer = $this->_htmlquickform->getRenderer();
        $renderer->setClassPrefix('plugg-');
        
        // Assign errors if any
        foreach ($this->_errors as $elementName => $messages) {
            if ($elementName == '') {
                // Form level error
                foreach ($messages as $message) {
                    $this->_htmlquickform->addHeader(sprintf('<div class="plugg-error">%s</div>', h($message)));
                }
            } else {
                // There can only be one error message for each form element..
                $renderer->setElementError($elementName, array_shift($messages));
                // Do not collapse so that the error message is visible to the user
                $renderer->removeElementClass($elementName, 'plugg-collapsed');
            }
        }
        
        if ($freeze) $this->_htmlquickform->freeze();
        
        return $elementsOnly ? $this->_htmlquickform->renderElements($renderer) : $this->_htmlquickform->render($renderer);
    }
}