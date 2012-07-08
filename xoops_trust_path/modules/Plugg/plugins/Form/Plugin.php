<?php
class Plugg_Form_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_Form_Field
{
    const FORM_FIELD_NORMAL = 0, FORM_FIELD_SYSTEM = 1;
    
    private $_elementHandlers = array(), $_initialized = false;
    private static $_forms = array();

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/form' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
            ),
            '/form/:form_id' => array(
                'controller' => 'Form',
                'format' => array(':form_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/form/:form_id':
                return ($this->_application->form = $this->getRequestedEntity($request, 'Form', 'form_id'))
                     && !$this->_application->form->hidden;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/form/:form_id':
                return $this->_application->form->title;
        }
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content/form' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/form/add' => array(
                'controller' => 'AddForm',
                'title' => $this->_('Add form'),
                'type' => Plugg::ROUTE_MENU,
            ),
            '/content/form/:form_id' => array(
                'controller' => 'Form',
                'format' => array(':form_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/content/form/:form_id/edit' => array(
                'controller' => 'Form_Edit',
                'title' => $this->_('Edit form'),
                'type' => Plugg::ROUTE_MENU,
                'title_callback' => true,
            ),
            '/content/form/:form_id/submit/fields' => array(
                'controller' => 'Form_SubmitFields',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/content/form/:form_id/edit/fields' => array(
                'controller' => 'Form_EditFields',
                'title' => $this->_('Field settings'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/form/:form_id/edit/field' => array(
                'controller' => 'Form_EditField',
                'title' => $this->_('Edit field'),
                'access_callback' => true,
            ),
            '/content/form/:form_id/edit/fieldset' => array(
                'controller' => 'Form_EditFieldset',
                'title' => $this->_('Edit fieldset'),
            ),
            '/content/form/:form_id/edit/mail_settings' => array(
                'controller' => 'Form_EditMailSettings',
                'title' => $this->_('Mail settings'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/form/:form_id/entries' => array(
                'controller' => 'Form_Entries',
                'title' => $this->_('View entries'),
            ),
            '/content/form/:form_id/entries/download' => array(
                'controller' => 'Form_Entries_Download',
                'title' => $this->_('Download entries'),
                'type' => Plugg::ROUTE_MENU,
            ),
            '/content/form/:form_id/entries/:formentry_id' => array(
                'controller' => 'Form_Entries_Entry',
                'title' => $this->_('View entry'),
                'access_callback' => true,
                'format' => array(':formentry_id' => '\d+'),
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/content/form/:form_id':
                return ($this->_application->form = $this->getRequestedEntity($request, 'Form', 'form_id')) ? true : false;

            case '/content/form/:form_id/edit/field':
                return ($this->_application->field = $this->getRequestedEntity($request, 'Field', 'field_id')) ? true : false;
                
            case '/content/form/:form_id/entries/:formentry_id':
                return ($this->_application->formentry = $this->getRequestedEntity($request, 'Formentry', 'formentry_id')) ? true : false;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/content/form/:form_id':
                return $this->_application->form->title;
                
            case '/content/form/:form_id/edit':   
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General settings') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */

    /* Start implementation of Plugg_Form_Field */

    public function formFieldGetFormElementTypes()
    {
        return array('yesno' => self::FORM_FIELD_NORMAL, 'textarea' => self::FORM_FIELD_NORMAL, 'radio' => self::FORM_FIELD_SYSTEM, 'radios' => self::FORM_FIELD_NORMAL,
            'checkbox' => self::FORM_FIELD_NORMAL, 'checkboxes' => self::FORM_FIELD_NORMAL, 'select' => self::FORM_FIELD_NORMAL, 'textmulti' => self::FORM_FIELD_NORMAL,
            'hidden' => self::FORM_FIELD_SYSTEM, 'item' => self::FORM_FIELD_NORMAL, 'markup' => self::FORM_FIELD_SYSTEM, 'password' => self::FORM_FIELD_NORMAL,
            'textfield' => self::FORM_FIELD_NORMAL, 'file' => self::FORM_FIELD_NORMAL, 'fieldset' => self::FORM_FIELD_SYSTEM,
            'submit' => self::FORM_FIELD_SYSTEM, 'email' => self::FORM_FIELD_NORMAL, 'url' => self::FORM_FIELD_NORMAL,
            'date' => self::FORM_FIELD_NORMAL, 'grid' => self::FORM_FIELD_SYSTEM, 'tableselect' => self::FORM_FIELD_NORMAL,
        );
    }
    
    public function formFieldGetTitle($type)
    {
        switch ($type) {
            case 'yesno':
                return $this->_('Yes/No selection');
            case 'textarea':
                return $this->_('Textarea field');
            case 'radio':
                return $this->_('Single radio button');
            case 'radios':
                return $this->_('Radio buttons');
            case 'checkbox':
                return $this->_('Single checkbox');
            case 'checkboxes':
                return $this->_('Checkboxes');
            case 'select':
                return $this->_('Drop-down list');
            case 'textmulti':
                return $this->_('Multi text input field');
            case 'hidden':
                return $this->_('Hidden field');
            case 'item':
                return $this->_('Static text field');
            case 'markup':
                return $this->_('HTML markup');
            case 'password':
                return $this->_('Password input field');
            case 'textfield':
                return $this->_('Text input field');
            case 'file':
                return $this->_('File upload field');
            case 'fieldset':
                return $this->_('Fieldset');
            case 'submit':
                return $this->_('Submit button');
            case 'email':
                return $this->_('Email input field');
            case 'url':
                return $this->_('URL input field');
            case 'date':
                return $this->_('Date selection');
            case 'grid':
                return $this->_('Grid');
            case 'tableselect':
                return $this->_('Table select field');
        }
    }
    
    public function formFieldGetSummary($type)
    {
        switch ($type) {
            case 'yesno':
                return $this->_('Displays yes/no selection options for users to choose from.');
            case 'textarea':
                return $this->_('Displays a textarea field.');
            case 'radio':
                return $this->_('Displays a single radio button.');
            case 'radios':
                return $this->_('Displays a set of radio buttons.');
            case 'checkbox':
                return $this->_('Displays a single checkbox.');
            case 'checkboxes':
                return $this->_('Displays a set of checkboxes.');
            case 'select':
                return $this->_('Displays a drop-down list or scrolling selection box.');
            case 'textmulti':
                return $this->_('Displays a textarea field where multiple text values are separated with separators.');
            case 'hidden':
                return $this->_('Stores data in a hidden form field.');
            case 'item':
                return $this->_('Displays a read-only text field.');
            case 'markup':
                return $this->_('Generates generic markup for display inside forms.');
            case 'password':
                return $this->_('Displays a password input field.');
            case 'textfield':
                return $this->_('Displays a text input field.');
            case 'file':
                return $this->_('Displays a file upload field.');
            case 'fieldset':
                return $this->_('Displays a group of form field items.');
            case 'submit':
                return $this->_('Displays a form submit button.');
            case 'email':
                return $this->_('Displays an e-mail input field.');
            case 'url':
                return $this->_('Displays a URL input field.');
            case 'date':
                return $this->_('Displays a date selection box.');
            case 'grid':
                return $this->_('Displays a group of form field items in a grid.');
            case 'tableselect':
                return $this->_('Displays a table of radio buttons or checkboxes.');
        }
    }

    public function formFieldGetFormElement($type, $name, array &$data, Plugg_Form_Form $form)
    {
        switch($type) {
            case 'fieldset':               
                $elements = array();
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;
                    foreach (array_keys($data['#children'][$weight]) as $_key) {
                        $_data =& $data['#children'][$weight][$_key];
                        // Append parent element name if #tree is true for the parent element and #tree is not set to false explicitly for the current element
                        if (!empty($data['#tree']) && (!$data['#tree_allow_override'] || false !== $_data['#tree'])) {
                            $_name = sprintf('%s[%s]', $name, $_key);
                            $_data['#tree'] = true;
                            if (!isset($_data['#value']) && isset($data['#value'][$_key])) $_data['#value'] = $data['#value'][$_key];
                            if (!isset($_data['#default_value']) && isset($data['#default_value'][$_key])) {
                                $_data['#default_value'] = $data['#default_value'][$_key];
                            }
                        } else {
                            $_name = $_key;
                        }
                        $_data['#tree_allow_override'] = $data['#tree_allow_override'];
                        $elements[] = $form->createElement($_data['#type'], $_name, $_data);
                    }
                }
                $data['#value'] = $data['#default_value'] = null;
                if (!isset($data['#separator'])) $data['#separator'] = '';
                return  $form->doCreateElement('group', $name, $data['#label'], $elements, $data['#separator']);
                
            case 'grid':
                $element = $form->doCreateElement('grid', $name, $data['#label'], array('actAsGroup' => true));
                if (!empty($data['#sortable'])
                    && !empty($data['#default_value']) // table must not be empty
                ) {
                    $element->setAttribute('class', 'plugg-horizontal plugg-sortable');
                } else {
                    $element->setAttribute('class', 'plugg-horizontal');
                }
                $element->setEmptyText(isset($data['#empty']) ? $data['#empty'] : $this->_('No records found.'));
                // Define columns
                $columns = array();
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;
                    
                    foreach (array_keys($data['#children'][$weight]) as $column_name) {
                        $columns[$column_name] =& $data['#children'][$weight][$column_name];
                        $element->addColumnName($columns[$column_name]['#title']);
                        $columns[$column_name]['#title'] = '';
                    }
                }
                // Add rows
                if (!isset($data['#default_value'])) $data['#default_value'] = array();
                foreach (array_keys($data['#default_value']) as $i) {
                    $row = array();
                    foreach (array_keys($columns) as $column_name) {
                        // Always prepend element name of the grid
                        $columns[$column_name]['#tree'] = true;
                        $columns[$column_name]['#tree_allow_override'] = false;
                        if ($columns[$column_name]['#type'] !== 'radio' || empty($columns[$column_name]['#single_value'])) {
                            $_name = sprintf('%s[%d][%s]', $name, $i, $column_name);
                            $row[] = $form->createElement($columns[$column_name]['#type'], $_name, $columns[$column_name]);
                            // Set default/constant values if any defined for this column. We will call
                            // the HTMLQuickform methods directly to avoid overriding the default settings
                            // of column data.
                            if (isset($data['#default_value'][$i][$column_name])) {
                                $form->setDefaultValues(array($_name => $data['#default_value'][$i][$column_name]));
                            }
                            if (isset($data['#value'][$i][$column_name])) {
                                $form->setConstantValues(array($_name => $data['#value'][$i][$column_name]));
                            }
                        } else {
                            // Only single value allowed for this radio element
                            $_name = sprintf('%s[0][%s]', $name, $column_name);
                            $row[] = $form->createElement($columns[$column_name]['#type'], $_name, $columns[$column_name]);
                        }
                    }
                    $element->addRow($row);
                }           
                
                return $element;
                
            case 'tableselect':
                if (!isset($data['#template'])) {
                    // Modify template slightly so that the field decription is displayed at the top of the table.
                    $data['#template'] = '<div class="{class_prefix}form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="{class_prefix}form-field-label"><span>{label}</span><!-- BEGIN required --><span class="{class_prefix}form-field-required">*</span><!-- END required --></div><!-- END label -->
  <div class="{class_prefix}form-field-content">
    <!-- BEGIN label_2 --><div class="{class_prefix}form-field-description {class_prefix}form-field-description-top">{label_2}</div><!-- END label_2 -->
    <!-- BEGIN error --><div class="{class_prefix}form-field-error"><!-- END error -->
    {element}<!-- BEGIN error --><span class="{class_prefix}form-field-error">{error}</span><!-- END error -->
    <!-- BEGIN error --></div><!-- END error -->
  </div>
</div>';
                }
                $element = $form->doCreateElement('grid', $name, $data['#label'], array('actAsGroup' => false));
                if (!empty($data['#sortable'])
                    && !empty($data['#options']) // table must not be empty
                ) {
                    $element->setAttribute('class', 'plugg-horizontal plugg-sortable');
                } else {
                    $element->setAttribute('class', 'plugg-horizontal');
                }
                $element->setEmptyText(isset($data['#empty']) ? $data['#empty'] : $this->_('No records found.'));
                // Add column for checkbox/radio elements if the tableselect element is disabled completely
                if (!$data['#disabled']) {
                    // Define columns
                    if (!empty($data['#multiple'])) {
                        $select_element_setting = array(
                            '#type' => 'checkbox',
                            '#value' => $data['#value'],
                            '#default_value' => @$data['#default_value'],
                        );
                        // Add a check-all button?
                        if (!isset($data['#js_select']) || false !== $data['#js_select']) {
                            $element->addColumnName('<input type="checkbox" class="checkTrigger" />');
                            $select_element_setting['#attributes']['class'] = 'checkTarget';
                        } else {
                            $element->addColumnName('');
                        }
                    } else {
                        $element->addColumnName('');
                        $select_element_setting = array(
                            '#type' => 'radio',
                            '#value' => $data['#value'],
                            '#default_value' => @$data['#default_value'],
                        );
                    }
                }
                if (isset($data['#footer']) && is_string($data['#footer'])) {
                    foreach ($data['#header'] as $header_name => $header_label) {
                        $header_attributes = isset($data['#header_attributes'][$header_name]) ? $data['#header_attributes'][$header_name] : null;
                        $element->addColumnName($header_label, $header_attributes);
                    }
                    $footer = $data['#footer'];
                } else {
                    $header_index = 0;
                    $footer = array();
                    foreach ($data['#header'] as $header_name => $header_label) {
                        $header_attributes = isset($data['#header_attributes'][$header_name]) ? $data['#header_attributes'][$header_name] : null;
                        $element->addColumnName($header_label, $header_attributes);
                        if (isset($data['#footer'][$header_name])) {
                            $footer[$header_index] = $data['#footer'][$header_name];
                        }
                        ++$header_index;
                    }
                }
                $element->setFooter($footer, isset($data['#footer_attributes']) ? $data['#footer_attributes'] : null); 
                // Add rows
                if (!isset($data['#options_disabled'])) $data['#options_disabled'] = array();
                if (!empty($data['#options'])) {
                    if (isset($select_element_setting)) $select_element_setting = $select_element_setting + $form->defaultElementSettings();
                    $item_element_setting = array('#type' => 'item') + $form->defaultElementSettings();
                    $empty_element_setting = array('#type' => 'markup', '#markup' => '') + $form->defaultElementSettings();   
                    foreach ($data['#options'] as $option_id => $option_labels) {
                        if (!is_array($option_labels)) {
                            if (!$option_labels = @array_combine(array_keys($data['#header']), explode(',', $option_labels))) {
                                continue;
                            }
                            $data['#options'][$option_id] = $option_labels;
                        }
                        $_name = sprintf('%s[0]', $name);
                        if (!$data['#disabled']) {
                            $select_element_setting['#return_value'] = $option_id;
                            if (in_array($option_id, $data['#options_disabled'])) {
                                // Since all select elements in the field have the same field name, we need to manually disable the element
                                // by adding the disabled attribute to disable each element individually
                                $select_element_setting['#attributes']['disabled'] = 'disabled'; // not allowed to select this option
                                unset($select_element_setting['#disabled']);
                            } else {
                                unset($select_element_setting['#attributes']['disabled'], $select_element_setting['#disabled']);
                            }
                            $row = array($form->createElement($select_element_setting['#type'], $_name, $select_element_setting));
                        } else {
                            // Do not display checkbox/radio if the tableselect element is disabled completely
                            $row = array();
                        }
                        foreach (array_keys($data['#header']) as $header_name) {
                            $item_element_setting['#markup'] = isset($option_labels[$header_name]) ? $option_labels[$header_name] : '';
                            $row[$header_name] = $form->createElement($item_element_setting['#type'], $_name . '[' . $header_name . ']', $item_element_setting);
                        }
                        $element->addRow($row, isset($data['#attributes'][$option_id]) ? $data['#attributes'][$option_id] : null);
                    }
                }
                $data['#default_value'] = $data['#value'] = null;
                return $element;

            case 'textfield':
            case 'email':
            case 'url':
                $data['#attributes']['maxlength'] = !empty($data['#maxlength']) ? $data['#maxlength'] : 255;
                if ($size = @$data['#size']) {
                    $data['#attributes']['size'] = $size;
                    if (!isset($data['#style']['width'])) {
                        $style = 'width:' . ceil($size * 0.7) . 'em;';
                        if (!isset($data['#attributes']['style'])) {
                            $data['#attributes']['style'] = $style;
                        } else {
                            $data['#attributes']['style'] .= $style;
                        }
                    }
                }
                return $form->doCreateElement('text', $name, $data['#label'], $data['#attributes']);

            case 'yesno':
                $data['#options'] = array(1 => $this->_('Yes'), 0 => $this->_('No'));
                $element = $form->doCreateElement('altselect', $name, $data['#label'], $data['#options'], $data['#attributes']);
                $element->setDelimiter(!empty($data['#delimiter']) ? $data['#delimiter'] : '&nbsp;');
                return $element;

            case 'textarea':
                $data['#attributes']['rows'] = !empty($data['#rows']) ? $data['#rows'] : 8;
                if ($cols = @$data['#cols']) {
                    $data['#attributes']['cols'] = $cols;
                    if (!isset($data['#style']['width'])) {
                        $style = 'width:' . ceil($cols * 0.7) . 'em;';
                        if (!isset($data['#attributes']['style'])) {
                            $data['#attributes']['style'] = $style;
                        } else {
                            $data['#attributes']['style'] .= $style;
                        }
                    }
                }
                return $form->doCreateElement('textarea', $name, $data['#label'], $data['#attributes']);
                
            case 'radio':
                if (!isset($data['#return_value'])) $data['#return_value'] = 1;
                $data['#options'] = array($data['#return_value'] => h($data['#label'][0]));
                $data['#label'][0] = '';
                $attr = array($data['#return_value'] => $data['#attributes']); // altselect element attributes must be set like this
                $element = $form->doCreateElement('altselect', $name, $data['#label'], $data['#options'] , $attr);
                return $element;

            case 'radios':
                $options = isset($data['#options']) ? array_map('h', $data['#options']) : array();
                if (!empty($options) && !empty($data['#options_description'])) {
                    // Additional description setting exists for options, so here we create a fieldset with multiple "radio" elements.
                    $fieldset = array(
                        '#type' => 'fieldset',
                        '#label' => $data['#label'],
                        '#children' => array(),
                        '#tree' => true,
                        '#tree_allow_override' => false,
                    ) + $form->defaultElementSettings();
                    foreach ($options as $option_value => $option_label) {
                        $fieldset['#children'][][0] = array(
                            '#type' => 'radio',
                            '#label' => array($option_label, @$data['#options_description'][$option_value]),
                            '#return_value' => $option_value,
                            '#default_value' => $data['#default_value'],
                            '#value' => $data['#value'],
                            '#attributes' => $data['#attributes'],
                            '#disabled' => isset($data['#options_disabled'][$option_value]),
                        ) +$form->defaultElementSettings();
                    }
                    $element = $form->createElement('fieldset', $name, $fieldset);
                } else {
                    $attributes = array('_qf_all' => $data['#attributes']);
                    if (!empty($data['#options_disabled'])) {
                        foreach ($data['#options_disabled'] as $option_value) {
                            $attributes[$option_value]['disabled'] = 'disabled';
                        }
                    }
                    $element = $form->doCreateElement('altselect', $name, $data['#label'], $options, $attributes);
                    if (!empty($data['#delimiter'])) $element->setDelimiter($data['#delimiter']);
                }
                return $element;
                
            case 'checkbox':
                $element = $form->createElement('radio', $name, $data);
                $element->setMultiple(true);
                return $element;

            case 'checkboxes':
                $options = isset($data['#options']) ? array_map('h', $data['#options']) : array();
                if (!empty($options) && !empty($data['#options_description'])) {
                    // Additional description setting exists for options, so here we create a fieldset with multiple "checkbox" elements.
                    $fieldset = array(
                        '#type' => 'fieldset',
                        '#label' => $data['#label'],
                        '#children' => array(),
                        '#tree' => true,
                        '#tree_allow_override' => false,
                    ) + $form->defaultElementSettings();
                    foreach ($options as $option_value => $option_label) {
                        $fieldset['#children'][][0] = array(
                            '#type' => 'checkbox',
                            '#title' => $option_label,
                            '#description' => @$data['#options_description'][$option_value],
                            '#return_value' => $option_value,
                            '#default_value' => $data['#default_value'],
                            '#value' => $data['#value'],
                            '#attributes' => $data['#attributes'],
                            '#disabled' => isset($data['#options_disabled'][$option_value]),
                        ) + $form->defaultElementSettings();
                    }
                    $element = $form->createElement('fieldset', $name, $fieldset);
                } else {
                    $attributes = array('_qf_all' => $data['#attributes']);
                    if (!empty($data['#options_disabled'])) {
                        foreach ($data['#options_disabled'] as $option_value) {
                            $attributes[$option_value]['disabled'] = 'disabled';
                        }
                    }
                    if (empty($data['#other']['enable'])) {
                        $element = $form->doCreateElement('altselect', $name, $data['#label'], $options, $attributes);
                    } else {
                        // Create attributes in the format that the altselect element expects
                        if (!empty($data['#other']['attributes'])) $attributes['_qf_other_text'] = $data['#other']['attributes'];

                        $element = $form->doCreateElement('altselect', $name, $data['#label'], $options, $attributes);
                        $element->setIncludeOther($data['#other']['type'] === 'textarea' ? 'textarea' : 'text');
                        $element->otherLabel = isset($data['#other']['label']) ? $data['#other']['label'] : $this->_('Other');
                        $element->otherTextMultiple = isset($data['#other']['text']) ? $data['#other']['text'] : $this->_('Other:');
                    }
                    $element->setMultiple(true);
                    if (!empty($data['#delimiter'])) $element->setDelimiter($data['#delimiter']);
                }
                return $element;

            case 'select':
                $options = isset($data['#options']) ? array_map('h', $data['#options']) : array();
                if (!empty($data['#multiple'])) {
                    $attr = array('size' => (10 < $count = count($options)) ? 10 : $count, 'multiple' => 'multiple');
                } else {
                    $attr = array('size' => 1);
                }
                return $form->doCreateElement('select', $name, $data['#label'], $options, array_merge($data['#attributes'], $attr));

            case 'textmulti':
                $data['#attributes']['rows'] = !empty($data['#rows']) ? $data['#rows'] : 8;
                if ($cols = @$data['#cols']) {
                    $data['#attributes']['cols'] = $cols;
                    if (!isset($data['#style']['width'])) {
                        $style = 'width:' . ceil($cols * 0.7) . 'em;';
                        if (!isset($data['#attributes']['style'])) {
                            $data['#attributes']['style'] = $style;
                        } else {
                            $data['#attributes']['style'] .= $style;
                        }
                    }
                }
                $element = $form->doCreateElement('textmulti', $name, $data['#label'], $data['#attributes']);
                if (isset($data['#separator']) && strlen($data['#separator'])) {
                    $element->setSeparator($data['#separator']);
                    if (isset($data['#key_value_separator'])
                        && strlen($data['#key_value_separator'])
                        && $data['#key_value_separator'] != $data['#separator']
                    ) {
                        $element->setKeyValueSeparator($data['#key_value_separator']);   
                    } else {
                        unset($data['#key_value_separator']);
                    }
                } else {
                    unset($data['#separator'], $data['#key_value_separator']);
                }
                
                return $element;

            case 'hidden':
                return $form->doCreateElement('hidden', $name);

            case 'item':
                if (!isset($data['#markup'])) {
                    if (!isset($data['#value'])) {
                        $data['#markup'] = isset($data['#default_value']) ? $data['#default_value'] : '';
                    } else {
                        $data['#markup'] = $data['#value'];
                    }
                }
                $data['#value'] = $data['#default_value'] = null;
                
                return $form->doCreateElement('static', $name, $data['#label'], $data['#markup']);
                
            case 'markup':
                if (!isset($data['#markup'])) {
                    if (!isset($data['#value'])) {
                        $data['#markup'] = isset($data['#default_value']) ? $data['#default_value'] : '';
                    } else {
                        $data['#markup'] = $data['#value'];
                    }
                }
                $data['#value'] = $data['#default'] = null;
                $data['#template'] = false; // no template
                
                return $form->doCreateElement('static', $name, null, $data['#markup']);

            case 'password':
                $data['#attributes']['autocomplete'] = 'off';
                $data['#attributes']['maxlength'] = !empty($data['#maxlength']) ? $data['#maxlength'] : 255;
                if ($size = @$data['#size']) {
                    $data['#attributes']['size'] = $size;
                    if (!isset($data['#style']['width'])) {
                        $style = 'width:' . ceil($size * 0.7) . 'em;';
                        if (!isset($data['#attributes']['style'])) {
                            $data['#attributes']['style'] = $style;
                        } else {
                            $data['#attributes']['style'] .= $style;
                        }
                    }
                }
                $element = $form->doCreateElement('password', $name, $data['#label'], $data['#attributes']);
                if (empty($data['#redisplay'])) {
                    // Do not display value in HTML to prevent password theft
                    $form->setConstantValues(array($name => ''));
                }
                $element->setPersistantFreeze(true);
                
                return $element;

            case 'submit':
                if (!isset($data['#value'])) {
                    if (!isset($data['#default_value'])) {
                        $data['#value'] = $this->_('Submit');
                    } else {
                        $data['#value'] = $data['#default_value'];
                    }
                }
                $data['#template'] = false; // no fancy template for submit buttons
                $data['#default_value'] = null;
                if (isset($data['#attributes']['class'])) {
                    $data['#attributes']['class'] .= ' button';
                } else {
                    $data['#attributes']['class'] = 'button';
                }
                return $form->doCreateElement('submit', $name, $data['#value'], $data['#attributes']);

            case 'file':
                $element = $form->doCreateElement('file', $name, $data['#label'], $data['#attributes']);
                if (!empty($data['#multiple'])) $element->setMultiple();
                return $element;

            case 'date':
                if (empty($data['#start_year'])) $data['#start_year'] = 1900;
                if (empty($data['#end_year'])) $data['#end_year'] = date('Y');
                $options = array(
                    'format' => $this->_('F d, Y'),
                    'minYear' => $data['#start_year'],
                    'maxYear' => $data['#end_year'],
                    'addEmptyOption' => !empty($data['#add_empty_option']),
                    'emptyOptionText' => '--',
                );
                if (!empty($data['#current_date_selected'])) $data['#default_value'] = time();
                return $form->doCreateElement('date', $name, $data['#label'], $options, $data['#attributes']);
        }
    }

    public function formFieldOnSubmitForm($type, $name, &$value, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'textfield':
            case 'password':
            case 'textarea':
                if (!$this->validateFormElementText($form, $name, $value, $data)) return;
                
                if (strlen($value) === 0) return;
                
                if (!empty($data['#char_validation'])
                    && in_array($data['#char_validation'], array('numeric', 'alnum', 'alpha'))
                ) {
                    $data['#' . $data['#char_validation']] = true;
                }

                if (!empty($data['#numeric'])) {
                    if (!is_numeric($value)) {
                        $form->setError($this->_('Input data must consist of numeric characters only.'), $name);

                        return;
                    }
                } elseif (!empty($data['#alpha'])) {
                    if (!ctype_alpha($value)) {
                        $form->setError($this->_('Input data must consist of alphabetic characters only.'), $name);

                        return;
                    }
                } elseif (!empty($data['#alnum'])) {
                    if (!ctype_alnum($value)) {
                        $form->setError($this->_('Input data must consist of alphanumeric characters only.'), $name);

                        return;
                    }
                }

                break;
                
            case 'textmulti':
                if (empty($data['#no_trim'])) array_map(array($this->_application, 'Trim'), $value);
                
                if (!empty($data['#char_validation'])
                    && in_array($data['#char_validation'], array('numeric', 'alnum', 'alpha'))
                ) {
                    $data['#' . $data['#char_validation']] = true;
                }
                
                if (!empty($data['#numeric'])) {
                    foreach ($value as $_value) {
                        if (!is_numeric($_value)) {
                            $form->setError($this->_('Input data must consist of numeric characters only.'), $name);

                            return;
                        }
                    }
                } elseif (!empty($data['#alpha'])) {
                    foreach ($value as $_value) {
                        if (!ctype_alpha($_value)) {
                            $form->setError($this->_('Input data must consist of alphabetic characters only.'), $name);

                            return;
                        }
                    }
                } elseif (!empty($data['#alnum'])) {
                    foreach ($value as $_value) {
                        if (!ctype_alnum($_value)) {
                            $form->setError($this->_('Input data must consist of alphanumeric characters only.'), $name);

                            return;
                        }
                    }
                }
                
                if (isset($data['#separator'])) {
                    // Regular expression defined for values?
                    if (!empty($data['#value_regex'])) {
                        // Make sure each value matches the defined regular expression
                        foreach ($value as $_value) {
                            if (!preg_match($data['#value_regex'], $_value)) {
                                $form->setError(sprintf($this->_('Invalid value: %s. Only lowercase alphanumeric characters and underscores are allowed.'), $_value), $name);
                                
                                return;
                            }
                        }
                    }
                    // Regular expression defined for keys?
                    if (!empty($data['#key_regex'])) {
                        // Make sure each key matches the defined regular expression
                        foreach ($value as $key => $_value) {
                            if (!preg_match($data['#key_regex'], $key)) {
                                $form->setError(sprintf($this->_('Invalid key: %s. Only lowercase alphanumeric characters and underscores are allowed.'), $key), $name);
                                
                                return;
                            }
                        }
                    }
                }
                
                break;

            case 'email':
                if (!$this->validateFormElementText($form, $name, $value, $data)) return;
                
                if (strlen($value) === 0) return;

                $this->validateFormElementEmail($form, $name, $value, $data);

                break;

            case 'url':
                if (!$this->validateFormElementText($form, $name, $value, $data)) return;

                if (strlen($value) === 0) return;

                $this->validateFormElementUrl($form, $name, $value, $data);

                break;
                
            case 'radio':
                // Is it a required field?
                if (is_null($value)) {
                    if ($data['#required']) {
                        $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    }

                    return;
                }

                if ($value != $data['#return_value']) {
                    $form->setError(sprintf($this->_('Invalid option selected.'), $data['#label'][0]), $name);

                    return;
                }
                
                break;
                
            case 'checkbox':
                // Is it a required field?
                if (!is_array($value) || count($value) === 0) {
                    if ($data['#required']) {
                        $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    }

                    return;
                }

                if ($value[0] != $data['#return_value']) {
                    $form->setError(sprintf($this->_('Invalid option selected.'), $data['#label'][0]), $name);

                    return;
                }
                
                break;

            case 'select':
            case 'tableselect':
            case 'checkboxes':
            case 'radios':
                // Is it a required field?
                if (is_null($value)) {
                    if ($data['#required']) {
                        $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    }

                    return;
                }
                
                // No options
                if (empty($data['#options'])) return;
                
                if ($type === 'tableselect' || !empty($data['#options_description'])) {
                    // The submitted value comes wrapped with an additional layer of array,
                    // so we remove that here to get the right one.
                    $value = $value[0];
                }

                if ($type !== 'checkboxes' || empty($data['#other']['enable'])) {
                    // Are all the selected options valid?
                    foreach ((array)$value as $_value) {
                        if (!in_array($_value, array_keys($data['#options']))) {
                            $form->setError(sprintf($this->_('Invalid option selected.'), $data['#label'][0]), $name);

                            return;
                        }
                    }
                } else {
                    // A custom value is allowed
                    foreach ((array)$value as $_value) {   
                        if (!in_array($_value, array_keys($data['#options']))) {
                            // A custom value

                            // Validate text value
                            if (!$this->validateFormElementText($form, $name, $_value, $data)) return;
                            
                            if (strlen($_value) === 0) return;
                            
                            if ($data['#other']['type'] === 'email') {
                                // Custom value must be a valid email address
                                if (!$this->validateFormElementEmail($form, $name, $_value, $data['#other'])) return;
                            } elseif ($data['#other']['type'] === 'url') {
                                // Custom value must be a valid url
                                if (!$this->validateFormElementUrl($form, $name, $_value)) return;
                            }
                        }
                    }
                }

                break;

            case 'yesno':
                // Is it a required field?
                if (is_null($value)) {
                    if ($data['#required']) {
                        $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    }

                    return;
                }

                // Is the selected option valid?
                if ($value[0] != 0 && $value[0] != 1) {
                    $form->setError(sprintf($this->_('Invalid option selected.'), $data['#label'][0]), $name);

                    return;
                }

                break;

            case 'date':
                // Is it a required field?
                if (empty($value)) {
                    if ($data['#required']) {
                        $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    }

                    return;
                }

                $year = $month = $day = 0;
                foreach ((array)$value as $format => $_value) {
                    switch ($format) {
                        case 'Y':
                        case 'y':
                            $year = intval($_value);
                            break;
                        case 'm':
                        case 'M':
                        case 'F':
                        case 'n':
                            $month = intval($_value);
                            break;
                        case 'd':
                        case 'j':
                            $day = intval($_value);
                            break;
                    }
                }
                
                if ($year && $month && $day) {
                    if (!empty($data['#check_date']) && !checkdate($month, $day, $year)) {
                        $form->setError($this->_('Invalid date.'), $name);
                        
                        return;
                    }
                } elseif (!$year && !$month && !$day && $data['#required']) {
                    $form->setError(sprintf($this->_('Selection required.'), $data['#label'][0]), $name);
                    
                    return;
                }
                
                // Make sure the submitted date falls between the specified start and end years
                if (!empty($year) && ($year < $data['#start_year'] || $year > $data['#end_year'])) {
                    $form->setError(sprintf($this->_('Invalid year.'), $data['#label'][0]), $name);
                    
                    return;
                }
                
                $value = array_merge($value, array('year' => $year, 'month' => $month, 'day' => $day));

                break;

            case 'fieldset':
                // Process child elements
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;

                    foreach (array_keys($data['#children'][$weight]) as $ele_key) {
                        $ele_data =& $data['#children'][$weight][$ele_key];
                        $ele_type = $ele_data['#type'];
                        if (!empty($data['#tree'])) {
                            if (is_null($value) || !array_key_exists($ele_key, $value)) {
                                if ($ele_type == 'submit') continue; // The button was not clicked
                                $value[$ele_key] = null;
                            }
                            $ele_name = sprintf('%s[%s]', $name, $ele_key);
                            if (false === $this->getElementHandler($ele_type)->formFieldOnSubmitForm($ele_type, $ele_name, $value[$ele_key], $ele_data, $form)
                                || $form->hasError()
                            ) continue;
                            // Copy the value to be used in subsequent validations
                            $ele_value = $value[$ele_key];
                        } else {
                            $ele_name = $ele_key;
                            // Since the name of element does not belongs to the group name hierarchy, we must fetch the element's value from the global scope
                            if (!isset($form->values[$ele_name])) {
                                if ($ele_type == 'submit') continue; // The button was not clicked
                                $form->values[$ele_name] = null;
                            }
                            if (false === $this->getElementHandler($ele_type)->formFieldOnSubmitForm($ele_type, $ele_name, $form->values[$ele_name], $ele_data, $form)
                                || $form->hasError()
                            ) continue;
                            // Copy the value to be used in subsequent validations
                            $ele_value = $form->values[$ele_name];
                        }

                        // Process custom validations if any
                        if (!empty($ele_data['#element_validate'])) {
                            foreach ($ele_data['#element_validate'] as $callback) {
                                if (!Plugg::processCallback($callback, array($form, $ele_value, $ele_name))
                                    || $form->hasError()
                                ) continue 2;
                            }
                        }

                        if ($ele_type == 'submit') {
                            // Save as clicked button
                            $form->setClickedButton($ele_data);
                    
                            // Append global validate/submit handlers if any set for this button
                            if (!empty($ele_data['#validate'])) {
                                foreach ($ele_data['#validate'] as $callback) {
                                    $form->settings['#validate'][] = $callback;
                                }
                            }
                            if (!empty($ele_data['#submit'])) {
                                foreach ($ele_data['#submit'] as $callback) {
                                    $form->settings['#submit'][] = $callback;
                                }
                            }
                        }
                    }
                }

                break;
                
            case 'grid':
                // Process child elements
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;

                    foreach (array_keys($data['#children'][$weight]) as $ele_key) {
                        $ele_data =& $data['#children'][$weight][$ele_key];
                        $ele_type = $ele_data['#type'];
                        foreach (array_keys($value) as $i) {
                            if (!isset($value[$i]) || !array_key_exists($ele_key, $value[$i])) {
                                if ($ele_type == 'submit') continue; // The button was not clicked
                                $value[$i][$ele_key] = null;
                            }
                            $ele_name = sprintf('%s[%d][%s]', $name, $i, $ele_key);
                            if (false === $this->getElementHandler($ele_type)->formFieldOnSubmitForm($ele_type, $ele_name, $value[$i][$ele_key], $ele_data, $form)
                                || $form->hasError()
                            ) continue;
                            // Copy the value to be used in subsequent validations
                            $ele_value = $value[$i][$ele_key];
                        
                            // Process custom validations if any
                            if (!empty($ele_data['#element_validate'])) {
                                foreach ($ele_data['#element_validate'] as $callback) {
                                    if (!Plugg::processCallback($callback, array($form, $ele_value, $ele_name))
                                        || $form->hasError()
                                    ) continue 2;
                                }
                            }
                        
                            if ($ele_type == 'submit') {
                                // Save as clicked button
                                $form->setClickedButton($ele_data);
                    
                                // Append global validate/submit handlers if any set for this button
                                if (!empty($ele_data['#validate'])) {
                                    foreach ($ele_data['#validate'] as $callback) {
                                        $form->settings['#validate'][] = $callback;
                                    }
                                }
                                if (!empty($ele_data['#submit'])) {
                                    foreach ($ele_data['#submit'] as $callback) {
                                        $form->settings['#submit'][] = $callback;
                                    }
                                }
                            }
                        }
                    }
                }

                break;

            case 'file':
                // Need to fetch value from $_FILES
                $file = $this->_getSubmittedFile($name);
                
                if (empty($data['#multiple'])) {
                    if (!$file || empty($file['tmp_name']) || $file['tmp_name'] == 'none' || $file['error'] == UPLOAD_ERR_NO_FILE) {
                        // No file
                        if ($data['#required']) $form->setError($this->_('File must be uploaded.'), $name);
                        
                        $value = null;
                    
                        return;
                    }
                } else {
                    if (!$file
                        || empty($file['tmp_name'])
                        || !isset($file['tmp_name'][0])
                        || $file['tmp_name'][0] == 'none'
                        || !isset($file['error'][0])
                        || $file['error'][0] == UPLOAD_ERR_NO_FILE
                    ) {
                        // No file
                        if ($data['#required']) $form->setError($this->_('File must be uploaded.'), $name);
                        
                        $value = null;
                    
                        return;
                    }
                }
                
                // Init uploader
                $uploader = $this->getFileUploader($data);
                
                if (!empty($data['#multiple'])) {
                    $value = array();
                    
                    // Get maximum number of upload files
                    $max_upload_num = intval(@$data['#multiple_max']);
                    
                    // Iterate through files data until the max limit is reached
                    foreach (array_keys($file['name']) as $i) {
                        $_file = array(
                            'name' => $file['name'][$i],
                            'type' => $file['type'][$i],
                            'size' => $file['size'][$i],
                            'tmp_name' => $file['tmp_name'][$i],
                            'error' => $file['error'][$i],
                        );
                        if (!$this->_uploadFile($uploader, $_file, $data)) {
                            $form->setError($_file['upload_error'], $name);

                            return false;
                        }
                        
                        // It's pointless to have the following attributes now 
                        unset($_file['tmp_name'], $_file['error'], $_file['no_file'], $_file['upload_error']);
                
                        $value[] = $_file;
                        
                        // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                        $data['#uploaded_files'][] = $_file['file_path'];
                    
                        --$max_upload_num;
                        if ($max_upload_num === 0) break;
                    }
                } else {
                    if (!$this->_uploadFile($uploader, $file, $data)) {
                        $form->setError($file['upload_error'], $name);

                        return false;
                    }
                    
                    // It's pointless to have the following attributes now 
                    unset($file['tmp_name'], $file['error'], $file['no_file'], $file['upload_error']);
                    
                    // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                    $data['#uploaded_files'][] = $file['file_path'];
                    
                    $value = $file;
                }

                break;
        }
    }
    
    private function _uploadFile($uploader, array &$file, array &$data)
    {
        $file = $uploader->uploadFile($file);
        
        if (!empty($file['upload_error'])) return false;
        
        $file['thumbnail'] = $file['thumbnail_path'] = $file['thumbnail_width'] = $file['thumbnail_height'] = null;

        if (!$file['is_image'] || empty($data['#thumbnail_width']) || empty($data['#thumbnail_height'])) return true;
        
        if (empty($data['#thumbnail_dir'])) $data['#thumbnail_dir'] = $uploader->uploadDir;

        // Generate thumbnail
        if (is_array($data['#thumbnail_width']) && is_array($data['#thumbnail_height'])) {
            $file['thumbnail'] = $file['thumbnail_path'] = $file['thumbnail_width'] = $file['thumbnail_height'] = array();
            foreach (array_keys($data['#thumbnail_width']) as $i) {
                if (!empty($data['#thumbnail_width'][$i])
                    && !empty($data['#thumbnail_height'][$i])
                    && ($file['image_width'] > $data['#thumbnail_width'][$i] || $file['image_height'] > $data['#thumbnail_height'][$i])
                ) {
                    // Get a unique file name under the image directory
                    $thumbnail = $uploader->getUniqueFileName($file['file_name'], $data['#thumbnail_dir']);
                    $thumbnail_path = $data['#thumbnail_dir'] . '/' . $thumbnail;
                    try {
                        $this->_generateThumbnail($file['file_path'], $thumbnail_path, $data['#thumbnail_width'][$i], $data['#thumbnail_height'][$i]);
                    } catch (Plugg_Exception $e) {
                        $file['upload_error'] = $e->getMessage();
                        // Clear all files
                        $file['thumbnail'] = $file['thumbnail_path'] = $file['thumbnail_width'] = $file['thumbnail_height'] = null;

                        return false;
                    }
            
                    // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                    $data['#uploaded_files'][] = $thumbnail_path;
            
                    if (!$thumbnail_size = @getimagesize($thumbnail_path)) {
                        $file['upload_error'] = $this->_('Failed fetching image size of thumbnail file.');
                        // Clear all files
                        $file['thumbnail'] = $file['thumbnail_path'] = $file['thumbnail_width'] = $file['thumbnail_height'] = null;

                        return false;
                    }
                    $file['thumbnail'][$i] = $thumbnail;
                    $file['thumbnail_path'][$i] = $thumbnail_path;
                    $file['thumbnail_width'][$i] = $thumbnail_size[0];
                    $file['thumbnail_height'][$i] = $thumbnail_size[1];
                } else {
                    $file['thumbnail'][$i] = $file['file_name'];
                    $file['thumbnail_path'][$i] = $file['file_path'];
                    $file['thumbnail_width'][$i] = $file['image_width'];
                    $file['thumbnail_height'][$i] = $file['image_height'];
                }
            }
        } else {
            if ($file['image_width'] > $data['#thumbnail_width'] || $file['image_height'] > $data['#thumbnail_height']) {
                // Get a unique file name under the image directory
                $thumbnail = $uploader->getUniqueFileName($file['file_name'], $data['#thumbnail_dir']);
                $thumbnail_path = $data['#thumbnail_dir'] . '/' . $thumbnail;
                try {
                    $this->_generateThumbnail($file['file_path'], $thumbnail_path, $data['#thumbnail_width'], $data['#thumbnail_height']);
                } catch (Plugg_Exception $e) {
                    $file['upload_error'] = $e->getMessage();

                    return false;
                }
            
                // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                $data['#uploaded_files'][] = $thumbnail_path;
            
                if (!$thumbnail_size = @getimagesize($thumbnail_path)) {
                    $file['upload_error'] = $this->_('Failed fetching image size of thumbnail file.');

                    return false;
                }
                $file['thumbnail'] = $thumbnail;
                $file['thumbnail_path'] = $thumbnail_path;
                $file['thumbnail_width'] = $thumbnail_size[0];
                $file['thumbnail_height'] = $thumbnail_size[1];
            } else {
                $file['thumbnail'] = $file['file_name'];
                $file['thumbnail_path'] = $file['file_path'];
                $file['thumbnail_width'] = $file['image_width'];
                $file['thumbnail_height'] = $file['image_height'];
            }
        }
        
        return true;
    }
    
    private function _generateThumbnail($original, $path, $width, $height)
    {
        $image_transform = $this->_application->getLocator()->createService('ImageTransform');
        $image_transform->load($original); // load original file
        $image_transform->fit($width, $height);
        $image_transform->save($path);
        $image_transform->free();
    }
    
    public function formFieldOnCleanupForm($type, $name, array $data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'fieldset':
            case 'grid':
                // Process child elements
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;

                    foreach ($data['#children'][$weight] as $ele_key => $ele_data) {
                        $ele_type = $ele_data['#type'];
                        $ele_name = empty($data['#tree']) ? $ele_key : sprintf('%s[%s]', $name, $ele_key);
                        $this->getElementHandler($ele_type)->formFieldOnCleanupForm($ele_type, $ele_name, $ele_data, $form);
                    }
                }
                
                break;
                
            case 'file':
                if ($form->isSubmitted() // form submission did not fail
                    || empty($data['#uploaded_files']) // no new file upload
                ) return;

                // Form submission failed, so remove the files that have been uploaded in the process
                foreach ($data['#uploaded_files'] as $file_path) @unlink($file_path);
                
                break;
        }
    }
    
    public function formFieldRenderHtml($type, $value, array $data, array $allValues = array())
    {
        switch ($type) {
            case 'textfield':
            case 'password':
            case 'textarea':
            case 'hidden':
            case 'email':
                return h($value);
                
            case 'markup':
            case 'item':
                return $value;
                
            case 'url':
                return sprintf('<a href="%1$s">%1$s</a>', h($value));
                
            case 'date':
                if (!empty($value['year']) && !empty($value['month']) && !empty($value['day'])) {
                    return date($this->_('F d, Y'), mktime(0, 0, 0, $value['month'], $value['day'], $value['year']));
                }
                if (!empty($value['month']) && !empty($value['day'])) {
                    return date($this->_('F d'), mktime(0, 0, 0, $value['month'], $value['day'], date('Y')));
                }
                
            case 'yesno':
                return $value == 1 ? $this->_('Yes') : $this->_('No');
                
            case 'checkbox':
            case 'radio':
            case 'radios':
                return isset($value) && isset($data['#options'][$value]) ? h($data['#options'][$value]) : '';
                
            case 'checkboxes':
                $ret = array();
                foreach ($value as $_value) {
                    if (isset($data['#options'][$_value])) $ret[] = $data['#options'][$_value];
                }
                return implode($this->_(', '), $ret);
                
            case 'select':
                if (empty($data['#multiple'])) {
                    return isset($value) && isset($data['#options'][$value]) ? $data['#options'][$value] : '';
                }
                $ret = array();
                foreach ($value as $_value) {
                    if (isset($data['#options'][$_value])) $ret[] = $data['#options'][$_value];
                }
                return implode($this->_(', '), $ret);

            case 'textmulti':
                return implode($this->_(', '), $ret);
                
            case 'file':
                return;
                
            case 'tableselect':
                $rows = array();
                foreach ((array)$value as $option_id) {
                    if (!isset($data['#options'][$option_id])) continue;
                    $option_labels = $data['#options'][$option_id];
                    if (!is_array($option_labels)) {
                        if (!$option_labels = @array_combine(array_keys($data['#header']), explode(',', $option_labels))) {
                            continue;
                        }
                        $data['#options'][$option_id] = $option_labels;
                    }
                    $row = array();
                    foreach (array_keys($data['#header']) as $header_name) {
                        $row[] = isset($option_labels[$header_name]) ? $option_labels[$header_name] : '';
                    }
                    $rows[] = implode('</td><td>', $row);
                }
                
                return sprintf('<table class="plugg-horizontal"><tr><th>%s</th></tr><tr><td>%s</td></tr></table>', implode('</th><th>', $data['#header']), implode('</td></tr><tr><td>', $rows));
                
            case 'fieldset':
                $html = array('<table class="plugg-vertical">');          
                foreach (array_keys($data['#children']) as $weight) {
                    if (!is_int($weight)) continue;
                    foreach (array_keys($data['#children'][$weight]) as $_key) {
                        $_data = $data['#children'][$weight][$_key];
                        // Append parent element name if #tree is true for the parent element and #tree is not set to false explicitly for the current element
                        if (!empty($data['#tree']) && (!$data['#tree_allow_override'] || false !== $_data['#tree'])) {
                            $_value = isset($value[$_key]) ? $value[$_key] : null;
                        } else {
                            $_value = isset($allValues[$_key]) ? $allValues[$_key] : null;
                        }
                        if (is_null($_value)) continue;
                        
                        $html[] = sprintf('<tr><th>%s</th><td>%s</td></tr>', h($_data['#title']), $this->formFieldRenderHtml($_data['#type'], $_value, $_data, $allValues));
                    }
                }
                $html[] = '</table>';
                
                return implode(PHP_EOL, $html);
        }
    }

    public function formFieldGetSettings($type, array $currentValues)
    {
        switch ($type) {
            case 'textfield':
                return array(
                    'size' => array(
                        '#title' => $this->_('Size of textfield'),
                        '#description' => $this->_('Enter the width of the field which will be used as the size attribute of the field. Leave the field blank to have it set by CSS (recommended).'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['size']) ? $currentValues['size'] : null,
                    ),
                    'default_value' => array(
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value of the field.'),
                        '#default_value' => isset($currentValues['default_value']) ? $currentValues['default_value'] : null,
                    ),
                    'no_trim' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Do not remove leading and trailing white spaces.'),
                        '#description' => $this->_('Leading and trailing white spaces are removed from the value the user entered. Check this option if you do not want this to happen automatically.'),
                        '#default_value' => array_key_exists('no_trim', $currentValues) ? !empty($currentValues['no_trim']) : false,
                    ),
                    'minlength' => array(
                        '#title' => $this->_('Minimum length'),
                        '#description' => $this->_('The minimum length of field value in characters.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['minlength']) ? $currentValues['minlength'] : null,
                    ),
                    'maxlength' => array(
                        '#title' => $this->_('Maximum length'),
                        '#description' => $this->_('The maximum length of field value in characters.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['maxlength']) ? $currentValues['maxlength'] : null,
                    ),
                    'char_validation' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Character validation'),
                        '#options' => array(
                            'numeric' => $this->_('Allow only numeric characters.'),
                            'alpha' => $this->_('Allow only alphabetic characters.'),
                            'alnum' => $this->_('Allow only alphanumeric characters.'),
                            'none' => $this->_('No validation.'),
                        ),
                        '#default_value' => isset($currentValues['char_validation']) ? $currentValues['char_validation'] : 'none',
                    ),
                    'field_prefix' => array(
                        '#title' => $this->_('Label placed to the left of the field'),
                        '#description' => $this->_('Examples: $, #, -.'),
                        '#size' => 15,
                        '#default_value' => isset($currentValues['field_prefix']) ? $currentValues['field_prefix'] : null,
                    ),
                    'field_suffix' => array(
                        '#title' => $this->_('Label placed to the right of the field'),
                        '#description' => $this->_('Examples: km, %, g.'),
                        '#size' => 15,
                        '#default_value' => isset($currentValues['field_suffix']) ? $currentValues['field_suffix'] : null,
                    ),
                );
            
            case 'item':
                return array(
                    'markup' => array(
                        '#type' => 'textarea',
                        '#title' => $this->_('HTML markup'),
                        '#description' => $this->_('Enter HTML that will be outout on the form.'),
                        '#rows' => 8,
                        '#default_value' => isset($currentValues['markup']) ? $currentValues['markup'] : null,
                    ),
                );
                
            case 'password':
                $text_settings = $this->formFieldGetSettings('textfield', $currentValues);
                unset($text_settings['field_prefix'], $text_settings['field_suffix']);
                return $text_settings;
            case 'url':
                $text_settings = $this->formFieldGetSettings('textfield', $currentValues);
                unset($text_settings['char_validation'], $text_settings['field_prefix'], $text_settings['field_suffix']);
                return array_merge(
                    $text_settings,
                    array(
                        'allowed_schemes' => array(
                            '#type' => 'textmulti',
                            '#rows' => 1,
                            '#separator' => ',',
                            '#title' => $this->_('Allowed URL schemes'),
                            '#description' => $this->_('Enter a list of allowed URL schemes, separated by commas.'), 
                            '#default_value' => isset($currentValues['url']['allowed_schemes']) ? $currentValues['url']['allowed_schemes'] : array('http', 'https'),
                            '#value_regex' => '/^[a-z0-9]+$/',
                        ),
                        'allow_localhost' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Allow localhost hostnames.'),
                            '#default_value' => !empty($currentValues['url']['allow_localhost']),
                        ),
                        'allow_ip' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Allow IP based hostnames.'),
                            '#default_value' => !empty($currentValues['url']['allow_ip']),
                        ),
                    )
                );
            case 'email':
                $text_settings = $this->formFieldGetSettings('textfield', $currentValues);
                unset($text_settings['char_validation'], $text_settings['field_prefix'], $text_settings['field_suffix']);
                return array_merge(
                    $text_settings,
                    array(
                        'mx' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Enable MX validation.'),
                            '#description' => $this->_('Check this option to validate the presence of an MX record on the hostname of the email address. Please be aware this will likely to slow your script down.'), 
                            '#default_value' => !empty($currentValues['email']['mx']),
                        ),
                        'allow_localhost' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Allow localhost email addresses.'),
                            '#default_value' => !empty($currentValues['email']['allow_localhost']),
                        ),
                        'allow_ip' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Allow IP based email addresses.'),
                            '#default_value' => !empty($currentValues['email']['allow_ip']),
                        ),
                    )
                );
            case 'yesno':
                return array(
                    'default_value' => array(
                        '#type' => 'yesno',
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value of the field.'),
                        '#default_value' => array_key_exists('default_value', $currentValues) ? !empty($currentValues['default_value']) : true,
                    ),
                    'delimiter' => array(
                        '#title' => $this->_('Delimiter'),
                        '#description' => $this->_('Enter an HTML string to separate the options'),
                        '#size' => 8,
                        '#default_value' => isset($currentValues['delimiter']) ? $currentValues['delimiter'] : '&nbsp;',
                    ),
                );
            case 'date':
                return array(
                    'start_year' => array(
                        '#title' => $this->_('Start year'),
                        '#description' => $this->_('Sets the start year for the year selection box.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#minlength' => 4,
                        '#maxlength' => 4,
                        '#default_value' => isset($currentValues['min_year']) ? $currentValues['min_year'] : 1900,
                    ),
                    'end_year' => array(
                        '#title' => $this->_('End year'),
                        '#description' => $this->_('Sets the start year for the year selection box. Leave this field blank to use the current year.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#minlength' => 4,
                        '#maxlength' => 4,
                        '#default_value' => isset($currentValues['max_year']) ? $currentValues['max_year'] : null,
                    ),
                    'check_date' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Check the validity of date.'),
                        '#default_value' => array_key_exists('check_date', $currentValues) ? !empty($currentValues['check_date']) : true,
                    ),
                    'add_empty_option' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow empty selection.'),
                        '#description' => $this->_('Check this option to allow empty selection of the date field.'),
                        '#default_value' => !empty($currentValues['add_empty_option']),
                    ),
                    'current_date_selected' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Set the current date selected by default.'),
                        '#default_value' => !empty($currentValues['current_date_selected']),
                    ),
                );
            case 'textarea':
                return array(
                    'rows' => array(
                        '#title' => $this->_('Rows'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['rows']) ? $currentValues['rows'] : 8,
                    ),
                    'cols' => array(
                        '#title' => $this->_('Columns'),
                        '#description' => $this->_('Enter the width of the field which will be used as the cols attribute of the field. Leave the field blank to have it set by CSS (recommended).'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['cols']) ? $currentValues['cols'] : '',
                    ),
                    'default_value' => array(
                        '#type' => 'textarea',
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value of the field.'),
                        '#rows' => 5,
                        '#default_value' => isset($currentValues['default_value']) ? $currentValues['default_value'] : null,
                    ),
                    'no_trim' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Do not remove leading and trailing white spaces.'),
                        '#description' => $this->_('Leading and trailing white spaces are removed from the value the user entered. Check this option if you do not want this to happen automatically.'),
                        '#default_value' => array_key_exists('no_trim', $currentValues) ? !empty($currentValues['no_trim']) : false,
                    ),
                    'char_validation' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Character validation'),
                        '#options' => array(
                            'numeric' => $this->_('Allow only numeric characters.'),
                            'alpha' => $this->_('Allow only alphabetic characters.'),
                            'alnum' => $this->_('Allow only alphanumeric characters.'),
                            'none' => $this->_('No validation.')
                        ),
                        '#default_value' => isset($currentValues['char_validation']) ? $currentValues['char_validation'] : 'none',
                    ),
                );
            case 'radios':
                return array(
                    'options' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Options'),
                        '#description' => $this->_('A list of selectable options, one option per line. Key-value pairs may be entered seperated by pipes, such as "option_value|Some readable option".'),
                        '#default_value' => isset($currentValues['options']) ? $currentValues['options'] : null,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'options_description' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Additional descriptions'),
                        '#description' => $this->_('Enter additional descriptions for the options above, one per line. Each description text must be prefixed by a corresponding option value and a pipe, such as "option_value|Description text goes here".'),
                        '#default_value' => isset($currentValues['options_description']) ? $currentValues['options_description'] : null,
                        '#collapsible' => true,
                        '#collapsed' => true,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'default_value' => array(
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('Enter the value selected by default.'),
                        '#default_value' => isset($currentValues['default_value']) ? $currentValues['default_value'] : null,
                        '#regex' => '/^[a-z0-9_]+$/',
                    ),
                    'delimiter' => array(
                        '#title' => $this->_('Delimiter'),
                        '#description' => $this->_('Enter an HTML string to separate the options.'),
                        '#size' => 8,
                        '#default_value' => isset($currentValues['delimiter']) ? $currentValues['delimiter'] : '&nbsp;',
                    ),
                );
            case 'checkbox':
                return array(
                    'default_value' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Checked by default.'),
                        '#description' => $this->_('Check this option to display the checkbox as checked by default.'),
                        '#default_value' => !empty($currentValues['default_value']),
                    ),
                );
            case 'checkboxes':
                return array(
                    'options' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Options'),
                        '#description' => $this->_('A list of selectable options. One option per line. Key-value pairs may be entered seperated by pipes, such as "safe_key|Some readable option".'),
                        '#default_value' => isset($currentValues['options']) ? $currentValues['options'] : null,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'options_description' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Additional descriptions'),
                        '#description' => $this->_('Enter additional descriptions for the options above, one per line. Each description text must be prefixed by a corresponding option value and a pipe, such as "option_value|Description text goes here".'),
                        '#default_value' => isset($currentValues['options_description']) ? $currentValues['options_description'] : null,
                        '#collapsible' => true,
                        '#collapsed' => true,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'default_value' => array(
                        '#type' => 'textmulti',
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value(s) of the field. For multiple selects use commas to separate multiple defaults.'),
                        '#rows' => 1,
                        '#separator' => ',',
                        '#default_value' => isset($currentValues['default']) ? $currentValues['default'] : null,
                        '#value_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'delimiter' => array(
                        '#title' => $this->_('Delimiter'),
                        '#description' => $this->_('Enter an HTML string to separate the options.'),
                        '#size' => 10,
                        '#default_value' => isset($currentValues['delimiter']) ? $currentValues['delimiter'] : '&nbsp;',
                    ),
                    'other' => array(
                        '#title' => $this->_('Custom value settings'),
                        '#collapsible' => true,
                        '#collapsed' => true,
                        'enable' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('Allow custom value'),
                            '#description' => $this->_('Check this option to allow the user to submit a custom value other than the options listed.'),
                            '#default_value' => !empty($currentValues['other']['enable']),
                        ),
                        'type' => array(
                            '#type' => 'radios',
                            '#title' => $this->_('Field type'),
                            '#description' => $this->_('Select the element type of custom value field.'),
                            '#options' => array(
                                'textfield' => $this->_('Text input field'),
                                'email' => $this->_('Email input field'),
                                'url' => $this->_('URL input field'),
                                'textarea' => $this->_('Textarea field'),
                            ),
                            '#default_value' => isset($currentValues['other']['type']) ? $currentValues['other']['type'] : 'textfield',
                        ),
                        'min_length' => array(
                            '#title' => $this->_('Minimum length'),
                            '#description' => $this->_('The minimum length of custom value in characters.'),
                            '#size' => 5,
                            '#numeric' => true,
                            '#default_value' => isset($currentValues['other']['min_length']) ? $currentValues['other']['min_length'] : null,
                        ),
                        'max_length' => array(
                            '#title' => $this->_('Maximum length'),
                            '#description' => $this->_('The maximum length of custom value in characters.'),
                            '#size' => 5,
                            '#numeric' => true,
                            '#default_value' => isset($currentValues['other']['max_length']) ? $currentValues['other']['max_length'] : null,
                        ),
                    ),
                );
            case 'select':
                return array(
                    'options' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Options'),
                        '#description' => $this->_('A list of selectable options. One option per line. Key-value pairs may be entered seperated by pipes, such as "safe_key|Some readable option".'),
                        '#default_value' => isset($currentValues['options']) ? $currentValues['options'] : null,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'multiple' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow multiple selections.'),
                        '#default_value' => !empty($currentValues['multiple']),
                    ),
                    'default_value' => array(
                        '#type' => 'textmulti',
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value of the field. For multiple selects use commas to separate multiple defaults.'),
                        '#rows' => 1,
                        '#separator' => ',',
                        '#default_value' => isset($currentValues['default_value']) ? $currentValues['default_value'] : null,
                        '#value_regex' => '/^[a-z0-9_]+$/',
                    )
                );
            case 'tableselect':
                return array(
                    'header' => array(
                        '#type' => 'textmulti',
                        '#rows' => 5,
                        '#separator' => PHP_EOL,
                        '#title' => $this->_('Table columns'),
                        '#description' => $this->_('Define table columns, one per line.'),
                        '#default_value' => isset($currentValues['header']) ? $currentValues['header'] : null,
                    ),
                    'options' => array(
                        '#type' => 'textmulti',
                        '#rows' => 7,
                        '#separator' => PHP_EOL,
                        '#key_value_separator' => '|',
                        '#title' => $this->_('Table rows'),
                        '#description' => $this->_('Define table rows, one per line. Each row must consist of a unique value and texts to be displayed under the columns defined above. The value and texts are separated by a pipe and the texts are separated by commas, e.g. "option1|Text for the 1st column,Text for the 2nd column,Text for the 3rd column", and the number of text strings must be identical with that of columns defined.'),
                        '#default_value' => isset($currentValues['options']) ? $currentValues['options'] : null,
                        '#key_regex' => '/^[a-z0-9_]+$/',
                    ),
                    'multiple' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow multiple selections.'),
                        '#default_value' => !empty($currentValues['multiple']),
                    ),
                    'default_value' => array(
                        '#type' => 'textmulti',
                        '#title' => $this->_('Default value'),
                        '#description' => $this->_('The default value of the field. For multiple selects use commas to separate multiple defaults.'),
                        '#rows' => 1,
                        '#separator' => ',',
                        '#default_value' => isset($currentValues['default_value']) ? $currentValues['default_value'] : null,
                        '#value_regex' => '/^[a-z0-9_]+$/',
                    )
                );
            
            case 'textmulti':
                return array_merge(
                    $this->formFieldGetSettings('textarea', $currentValues),
                    array(
                        'separator' => array(
                            '#title' => $this->_('Separator'),
                            '#description' => $this->_('Enter a string to separate the values.'),
                            '#size' => 5,
                            '#default_value' => isset($currentValues['delimiter']) ? $currentValues['delimiter'] : ',',
                        ),
                        'key_value_separator' => array(
                            '#title' => $this->_('Key-value separator'),
                            '#description' => $this->_('Enter a string to separate each key and value.'),
                            '#size' => 5,
                            '#default_value' => isset($currentValues['key_value_separator']) ? $currentValues['key_value_separator'] : '=',
                        ),
                    )
                );
            case 'file':
                return array(
                    'allowed_extensions' => array(
                        '#type' => 'textmulti',
                        '#rows' => 3,
                        '#separator' => ',',
                        '#title' => $this->_('Allowed file extensions'),
                        '#description' => $this->_('Enter a list of allowed file extensions separated by commas.'),
                        '#default_value' => isset($currentValues['allowed_extensions']) ? $currentValues['allowed_extensions'] : array('gif', 'jpg', 'jpeg', 'png'),
                        '#value_regex' => '/^[a-z0-9\.]+$/',
                    ),
                    'allow_only_images' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow only image files to be uploaded.'),
                        '#description' => $this->_('Check this option to allow only image files to be uploaded. If enabled, this setting will override the allowed file extensions setting.'),
                        '#default_value' => !empty($currentValues['allow_only_images']),
                    ),
                    'max_file_size' => array(
                        '#title' => $this->_('Maximum file size'),
                        '#description' => $this->_('The maximum file size of uploaded files in kilobytes. Leave this field blank for no limit.'),
                        '#size' => 7,
                        '#numeric' => true,
                        '#field_suffix' => 'KB',
                        '#default_value' => isset($currentValues['max_file_size']) ? $currentValues['max_file_size'] : 100,
                    ),
                    'max_image_width' => array(
                        '#title' => $this->_('Maximum image width'),
                        '#description' => $this->_('The maximum width of uploaded image files in pixels. Leave this field blank for no limit.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#field_suffix' => 'px',
                        '#default_value' => isset($currentValues['max_image_width']) ? $currentValues['max_image_width'] : null,
                    ),
                    'max_image_height' => array(
                        '#title' => $this->_('Maximum image height'),
                        '#description' => $this->_('The maximum height of uploaded image files in pixels. Leave this field blank for no limit.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#field_suffix' => 'px',
                        '#default_value' => isset($currentValues['max_image_height']) ? $currentValues['max_image_height'] : null,
                    ),
                    'multiple' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Allow multiple file upload in a single sutmit.'),
                        '#description' => $this->_('This feature requires javascript to be enabled on the browser. If not, then only 1 file at a time can be uploaded.'),
                        '#default_value' => !empty($currentValues['multiple']),
                    ),
                    'multiple_max' => array(
                        '#title' => $this->_('Maximum number of file uploads per submit'),
                        '#description' => $this->_('Enter the number of files allowed to upload per form submit. Leave the field blank for no limit.'),
                        '#size' => 5,
                        '#numeric' => true,
                        '#default_value' => isset($currentValues['multiple_max']) ? $currentValues['multiple_max'] : 2,
                    ),
                    'file_name_prefix' => array(
                        '#title' => $this->_('File name prefix'),
                        '#description' => $this->_('Uploaded file names will be prefixed with this value. Since each uploaded file will be automatically renamed to a unique file name, this setting is not required to prevent file name collisions.'),
                        '#size' => 10,
                        '#default_value' => isset($currentValues['file_name_prefix']) ? $currentValues['file_name_prefix'] : 'Form_',
                    ),
                );
        }
        
        return array();
    }

    public function validateFormElementText(Plugg_Form_Form $form, $name, &$value, array $data)
    {
        if (empty($data['#no_trim'])) $value = $this->_application->Trim($value);
        if (strlen($value) === 0) {
            if (!empty($data['#required'])) {
                $form->setError($this->_('must not be blank'), $name);

                return false;
            }
            
            return true;
        }
        
        // Check min/max length
        $min_length = empty($data['#minlength']) ? 0 : $data['#minlength'];
        $max_length = empty($data['#maxlength']) ? null : $data['#maxlength'];
        if ($min_length || $max_length) {
            $validator = new Zend_Validate_StringLength($min_length, $max_length, SABAI_CHARSET);
            if (!$validator->isValid($value)) {
                if ($min_length && $max_length) {
                    $form->setError(sprintf($this->_('must be between %d and %d characters.'), $min_length, $max_length), $name);
                } elseif (!$min_length) {
                    $form->setError(sprintf($this->_('must be shorter than %d characters.'), $max_length), $name);
                } else {
                    $form->setError(sprintf($this->_('must be longer than %d characters.'), $min_length), $name);
                }

                return false;
            }
        }
        
        if (!empty($data['#regex'])) {
            if (!preg_match($data['#regex'], $value, $matches)) {
                $form->setError($this->_('Invalid value.'), $name);
                
                return false;
            }
        }

        return true;
    }
    
    public function validateFormElementEmail(Plugg_Form_Form $form, $name, &$email, array $data)
    {
        $conf['allow'] = Zend_Validate_Hostname::ALLOW_DNS;
        $conf['mx'] = !empty($data['#mx']);
        // Allow localhost email addresses?
        if (!empty($data['#allow_localhost'])) {
            $conf['allow'] = $conf['allow'] | Zend_Validate_Hostname::ALLOW_LOCAL;
        }
        // Allow IP based email addresses?
        if (!empty($data['#allow_ip'])) {
            $conf['allow'] = $conf['allow'] | Zend_Validate_Hostname::ALLOW_IP;
        }
        // Validate email address
        $validator = new Zend_Validate_EmailAddress($conf);
        if (!$validator->isValid($email)) {
            $form->setError(
                sprintf($this->_('Invalid email address. Error: %s'), implode(' ', $validator->getMessages())),
                $name
            );

            return false;
        }
        
        return true;
    }
    
    public function validateFormElementUrl(Plugg_Form_Form $form, $name, &$url, $data)
    {
        // Below regex from PEAR Validate package
        $regex = '&^(?:([a-z][-+.a-z0-9]*):)?                             # 1. scheme
            (?://                                                   # authority start
            (?:((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();:\&=+$,])*)@)?    # 2. authority-userinfo
            (?:((?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[a-z0-9]+)?\.?)  # 3. authority-hostname OR
            |([0-9]{1,3}(?:\.[0-9]{1,3}){3}))                       # 4. authority-ipv4
            (?::([0-9]*))?)                                        # 5. authority-port
            ((?:/(?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'():@\&=+$,;])*)*/?)? # 6. path
            (?:\?([^#]*))?                                          # 7. query
            (?:\#((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();/?:@\&=+$,])*))? # 8. fragment
            $&xi';
                
        // Validate URL
        if (!preg_match($regex, $url, $matches)) {
            $form->setError($this->_('Invalid URL.'), $name);
            
            return false;
        }

        if (!empty($data['#allowed_schemes'])) {
            if ((!$scheme = parse_url($url, PHP_URL_SCHEME))
                || !in_array($scheme, $data['#allowed_schemes'])
            ) {
                $form->setError(
                    sprintf($this->_('Invalid URL scheme. Only the following URL shemes are permitted: %s'), implode(', ', $data['allowed_schemes'])),
                    $name
                );
            
                return false;
            } 
        }
        
        $conf['allow'] = Zend_Validate_Hostname::ALLOW_DNS;
        // Allow localhost email addresses?
        if (!empty($data['#allow_localhost'])) {
            $conf['allow'] = $conf['allow'] | Zend_Validate_Hostname::ALLOW_LOCAL;
        }
        // Allow IP based email addresses?
        if (!empty($data['#allow_ip'])) {
            $conf['allow'] = $conf['allow'] | Zend_Validate_Hostname::ALLOW_IP;
        }
        // Validate email address
        $validator = new Zend_Validate_Hostname($conf);
        if ((!$hostname = parse_url($url, PHP_URL_HOST)) || !$validator->isValid($hostname)) {
            $form->setError(
                sprintf($this->_('Invalid URL. Error: %s'), implode(' ', $validator->getMessages())),
                $name
            );

            return false;
        }

        return true;
    }
    
    public function getFileUploader($data)
    {
        $conf = array();
        if (!empty($data['#allowed_extensions'])) {
            $conf['allowedExtensions'] = $data['#allowed_extensions'];
        }
        $conf['imageOnly'] = !empty($data['#allow_only_images']);
        if (!empty($data['#max_file_size'])) {
            $conf['maxSize'] = $data['#max_file_size'] * 1024;
        }
        if (!empty($data['#max_image_width'])) {
            $conf['maxImageWidth'] = $data['#max_image_width'];
        }
        if (!empty($data['#max_image_height'])) {
            $conf['maxImageHeight'] = $data['#max_image_height'];
        }
        if (!empty($data['#file_name_prefix'])) {
            $conf['filenamePrefix'] = $data['#file_name_prefix'];
        }
        if (!empty($data['#upload_dir'])) {
            $conf['uploadDir'] = $data['#upload_dir'];
        }
        
        return $this->_application->getLocator()->createService('Uploader', $conf);
    }
    
    private function _getSubmittedFile($elementName)
    {
        if (empty($_FILES)) return false;
        
        if (isset($_FILES[$elementName])) return $_FILES[$elementName];
        
        if (false === $pos = strpos($elementName, '[')) return false;
        
        $base = substr($elementName, 0, $pos); 
        $key = str_replace(array(']', '['), array('', '"]["'), substr($elementName, $pos + 1, -1));
        $code = array(sprintf('if (!isset($_FILES["%s"]["name"]["%s"])) return false;', $base, $key));
        $code[] = '$file = array();';
        foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $property) {
            $code[] = sprintf('$file["%1$s"] = $_FILES["%2$s"]["%1$s"]["%3$s"];', $property, $base, $key);
        }
        $code[] = 'return $file;';

        return eval(implode(PHP_EOL, $code));
    }

    /* End implementation of Plugg_Form_Field */

    public function buildForm(array $settings, $useCache = true, array $values = null)
    {
        if (!isset($settings['#build_id']) || strlen($settings['#build_id']) !== 32) {
            $settings['#build_id'] = md5(uniqid(mt_rand(), true));
        } else {
            // Is the form already built and cached?
            if (isset(self::$_forms[$settings['#build_id']])) {
                // Return cached form if rebuild is not necessary
                if ($useCache) return self::$_forms[$settings['#build_id']];
            }
        }
        
        // Initialize form storage
        $storage = array();
        if (!empty($settings['#enable_storage'])) {
            if (isset($settings['#initial_storage'])) $storage = $settings['#initial_storage'];
            
            // Embed build ID in hidden field so that session data can be retrieved in subsequent requests using the ID as key
            $settings['_form_build_id'] = array(
                '#type' => 'hidden',
                '#value' => $settings['#build_id']
            );
        }

        if (isset($settings['#id'])) {
            // Allow other plugins to modify form settings and storage
            $this->_application->DispatchEvent(
                'FormBuildForm',
                array($settings['#id'], &$settings, &$storage)
            );
            $this->_application->DispatchEvent(
                'FormBuild' . implode('', array_map('ucfirst', explode('_', $settings['#id']))),
                array(&$settings, &$storage)
            );
        }

        $form = new Plugg_Form_Form($this, $settings, $storage);
        $form->build($values);

        // Add built form to cache
        self::$_forms[$settings['#build_id']] = $form;

        return $form;
    }
    
    public function submitForm($form)
    {
        $form = $form instanceof Plugg_Form_Form ? $this->buildForm($form->settings, !$form->rebuild, $form->values) : $this->buildForm($form);
        
        if ($this->_doSubmitForm($form)) {
            if (!empty($form->settings['#enable_storage'])) {
                // Save form storage data into session so that it can be retrieved in subsequent steps
                $this->setSessionVar($form->settings['#build_id'], $form->storage);
            }
            $form->setSubmitted();
        }
        
        // Allow form elements to cleanup things
        $form->cleanup();
        
        return $form->isSubmitted();
    }
    
    private function _doSubmitForm(Plugg_Form_Form $form)
    {
        if (!$form->submit() || !$form->hasClickedButton()) return false;
        
        // Call form level validation callbacks
        if (!empty($form->settings['#validate'])) {
            foreach ($form->settings['#validate'] as $callback) {
                Plugg::processCallback($callback, array($form));
            }
        }

        if ($form->hasError()) return false;

        // Call submit callbacks
        if (!empty($form->settings['#submit'])) {
            foreach ($form->settings['#submit'] as $callback) {
                // Abort immediately if the callback returned false or any error has been added
                if (false === Plugg::processCallback($callback, array($form))
                    || $form->hasError()
                ) return false;
            }
        }
        
        return true;
    }
    
    public function renderForm($form, $elementsOnly = false, $freeze = false)
    {
        // Call pre-render callbacks
        if (!empty($form->settings['#pre_render'])) {
            foreach ($form->settings['#pre_render'] as $callback) {
                Plugg::processCallback($callback, array($form));
            }
        }
 
        $form = $form instanceof Plugg_Form_Form ? $this->buildForm($form->settings, !$form->rebuild, $form->values) : $this->buildForm($form);

        $form_html = $form->render($elementsOnly, $freeze);
     
        // Call post-render callbacks
        if (!empty($form->settings['#post_render'])) {
            foreach ($form->settings['#post_render'] as $callback) {
                Plugg::processCallback($callback, array($form, &$form_html));
            }
        }

        if (!empty($form->settings['#enable_storage']) && !$form->isSubmitted() && !$elementsOnly && !$freeze) {
            // Save form storage into session
            $this->setSessionVar($form->settings['#build_id'], serialize($form->storage));
        }
 
        return $form_html;
    }

    public function getElementHandler($elementType)
    {
        if (!$this->_initialized) {
            $this->_initialize();
            $this->_initialized = true;
        }
        
        if (!isset($this->_elementHandlers[$elementType])) {
            throw new Plugg_Exception(sprintf($this->_('Invalid form element type: %s'), $elementType));
        }
        
        return $this->_application->getPlugin($this->_elementHandlers[$elementType]);
    }

    private function _initialize()
    {
        if (!class_exists('Sabai_HTMLQuickForm', false)) {
            require 'Sabai/HTMLQuickForm.php';
        }

        // Allow other plugins to register custom form fields
        $plugins = $this->_application->getPluginManager()->getInstalledPluginsByInterface('Plugg_Form_Field');
        foreach (array_keys($plugins) as $plugin_name) {
            if (!$plugin = $this->_application->getPlugin($plugin_name)) continue;
            foreach (array_keys($plugin->formFieldGetFormElementTypes()) as $element_type) {
                $this->_elementHandlers[$element_type] = $plugin_name;
            }
        }

        // Allow other plugins to register custom HTML_Quickform elements
        $element_types = array();
        $this->_application->DispatchEvent('FormBuilderInit', array(&$element_types));
        foreach ($element_types as $type) {
            Sabai_HTMLQuickForm::registerElementType($type['name'], $type['file'], $type['class']);
        }
    }
    
    public function getFieldData($excludeSystemFields = true)
    {
        // Fetch available fields and data
        $ret = array();
        if ($excludeSystemFields) {
            $fields = $this->getModel()->Field->criteria()->system_isNot(1)->fetch(0, 0, 'field', 'ASC');
        } else {
            $fields = $this->getModel()->Field->fetch(0, 0, 'field', 'ASC');
        }
        foreach ($fields as $field) {
            // skip if plugin of the field is not enabled
            if (!$field_plugin = $this->_application->getPlugin($field->plugin)) continue;

            $ret[$field->id] = array(
                'id' => $field->id,
                'type' => $field->type,
                'title' => $field_plugin->formFieldGetTitle($field->type),
                'summary' => $field_plugin->formFieldGetSummary($field->type),
                'settings' => $field_plugin->formFieldGetSettings($field->type, array()),
                'plugin' => $field_plugin->nicename,
            );
        }

        return $ret;
    }
    
    public function onFormFieldInstalled($pluginEntity, $plugin)
    {
        if ($fields = $plugin->formFieldGetFormElementTypes()) {
            $this->_createPluginFields($pluginEntity->name, $fields);
        }
    }

    public function onFormFieldUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginFields($pluginEntity->name);
    }

    public function onFormFieldUpgraded($pluginEntity, $plugin)
    {
        if (!$fields = $plugin->formFieldGetFormElementTypes()) {
            $this->_deletePluginFields($pluginEntity->name);
        } else {         
            $fields_already_installed = array();
            foreach ($this->getModel()->Field->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_field) {
                if (in_array($current_field->type, $fields)) {
                    $fields_already_installed[] = $current_field->type;
                    if ($type = @$fields[$current_field->type]) {
                        $current_widget->type = $type; // Update the field type if configured explicitly
                    }
                } else {
                    $current_field->markRemoved();
                }
            }
            $this->_createPluginFields(
                $pluginEntity->name,
                array_diff($fields, $fields_already_installed)
            );
            
        }
    }

    private function _createPluginFields($pluginName, $fields)
    {
        $model = $this->getModel();
        
        foreach ($fields as $field_type => $field_kind) {
            if (empty($field_type)) continue;
            $field = $model->create('Field');
            $field->type = $field_type;
            $field->system = ($field_kind & self::FORM_FIELD_SYSTEM) ? 1 : 0;
            $field->plugin = $pluginName;
            $field->markNew();
        }
        
        return $model->commit();;
    }

    private function _deletePluginFields($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Field->criteria()->plugin_is($pluginName)->fetch() as $field) {
            $field->markRemoved();
        }

        return $model->commit();
    }
    
    public function getEmailTags(Plugg_Form_Model_Form $form, Plugg_Form_Model_Formentry $formEntry = null)
    {
        return array(
            '{site_name}' => $this->_application->SiteName(),
            '{site_url}' => $this->_application->SiteUrl(),
            '{form_title}' => $form->title,
            '{form_url}' => $this->_application->getUrl('/form/' . $form->id),
            '{form_admin_url}' => $this->_application->getUrl('/content/form/' . $form->id),
            '{form_entry_admin_url}' => $formEntry ? $this->_application->getUrl('/content/form/' . $form->id . '/' . $formEntry->id) : '',
        );
    }
    
    public function getFieldsetField()
    {
        return $this->getModel()->Field->criteria()->type_is('fieldset')->fetch()->getFirst();
    }
    
    public function createDefaultFieldset(Plugg_Form_Model_Form $form, $commit = false)
    {
        // Create the default fieldset
        if (!$fieldset_field = $this->getFieldsetField()) {
            return false; // this should never happen but just in case
        }
        
        $fieldset = $form->createFormfield();
        $fieldset->assignField($fieldset_field);
        $fieldset->markNew();
        $fieldset->name = 'default';
        $fieldset->settings = serialize(array());
        
        if ($commit) return $fieldset->commit() ? $fieldset : false;
        
        return $fieldset;
    }
    
    public function getFormFields(Plugg_Form_Model_Form $form)
    {
        if (!$fieldset_field = $this->getFieldsetField()) {
            // this should never happen but just in case
            return false;
        }
        
        $formfields = array();
        foreach ($this->getModel()->Formfield->fetchByForm($form->id, 0, 0, 'weight', 'ASC')->with('Field') as $formfield) {
            if ($formfield->fieldset == 0) {
                if ($formfield->field_id != $fieldset_field->id) {
                    // This field does not have a parent fieldset but not a fieldset
                    $formfields_no_parent[] = $formfield;
                    continue;
                }
                // Is it the default fieldset?
                if ($formfield->name == 'default') $default_fieldset = $formfield;
            }
            $formfields[$formfield->fieldset][] = $formfield;
        }
        // Place formfields without parent under the default fieldset
        if (!empty($formfields_no_parent)) {
            if (empty($default_fieldset)) {
                if (!$default_fieldset = $this->createDefaultFieldset($form, true)) {
                    return false;
                }
            }
            foreach ($formfields_no_parent as $formfield_no_parent) {
                $formfields[$default_fieldset->id][] = $formfield_no_parent;
            }
        }
        
        return $formfields;
    }
    
    public function getAdminFieldsJS($formHtmlId, $editFieldUrl, $editFieldsetUrl)
    {
        return sprintf('
jQuery("td.form-fields-available dt").draggable({
    connectToSortable: "td.form-fields-active .form-fields-fields",
    helper: "clone",
    revert: "invalid",
    handle: "a.draggableHandle",
});
jQuery("td.form-fields-active").sortable({
    axis: "y",
    containment: "td.form-fields-active",
    items: ".form-fields-fieldset",
    cancel: ".form-fields-fieldset-deleted",
    helper: "clone",
    revert: true,
    opacity: 0.6,
    cursor: "move",
    placeholder: "form-fields-fieldset-placeholder",
    forcePlaceholderSize: true,
    handle: "a.draggableHandle",
});
var $form_fields_sortable_conf = {
    axis: "y",
    containment: ".form-fields-active",
    items: ".form-fields-field",
    cancel: ".form-fields-field-deleted",
    connectWith: ".form-fields-active .form-fields-fields",
    helper: "clone",
    revert: true,
    opacity: 0.6,
    cursor: "move",
    placeholder: "form-fields-field-placeholder",
    forcePlaceholderSize: true,
    handle: "a.draggableHandle",
    stop: function(event, ui) {
        if (ui.item.find(".form-fields-field-control").is(":hidden")) {
            var $fieldset = ui.item.closest(".form-fields-fieldset");
            if ($fieldset.hasClass("form-fields-fieldset-deleted")) {
                $fieldset.fadeTo("medium", "1.0").removeClass("form-fields-fieldset-deleted");
            }
            ui.item.find(".form-fields-field-control").show().find("a.form-fields-field-edit").click(function() {
                var $field_form_id = ui.item.attr("id") + Math.floor(Math.random()*100);
                var $field_form_url = "%1$s" + ("%1$s".indexOf("?", 0) === -1 ? "?" : "&") + "%2$s=" + encodeURIComponent($field_form_id) + "&field_id=" + ui.item.find(".form-fields-field-type").attr("value") + "&formfield_id=" + ui.item.find(".form-fields-field-id").attr("value");
                ui.item.find(".form-fields-field-form").attr("id", $field_form_id);
                // Clear id of the clone
                ui.item.attr("id");
                jQuery.plugg.ajax({
                    effect: "slide",
                    type: "get",
                    target: "#" + $field_form_id,
                    dataType: "html",
                    url: $field_form_url
                });
                return false;
            }).click().end().find("a.form-fields-field-delete").click(function() {
                if (ui.item.find(".form-fields-field-id").attr("value")) {
                    ui.item.find(".form-fields-field-form").slideUp("fast").end()
                        .fadeTo("medium", "0.4").addClass("form-fields-field-deleted");
                } else {
                    // Not yet saved
                    ui.item.remove();
                }
                return false;
            });
        }
    }
}
jQuery("td.form-fields-active .form-fields-fields").sortable($form_fields_sortable_conf).find(".form-fields-field-control").show().find(".form-fields-field-edit").click(function() {
    var $field = jQuery(this).closest(".form-fields-field");
    var $field_form_id = $field.find(".form-fields-field-form").attr("id");
    var $field_form_url = "%1$s" + ("%1$s".indexOf("?", 0) === -1 ? "?" : "&") + "%2$s=" + encodeURIComponent($field_form_id) + "&field_id=" + $field.find(".form-fields-field-type").attr("value") + "&formfield_id=" + $field.find(".form-fields-field-id").attr("value");
    jQuery.plugg.ajax({
        effect: "slide",
        type: "get",
        target: "#" + $field_form_id,
        dataType: "html",
        url: $field_form_url
    });
    return false;
}).end().find("a.form-fields-field-delete").click(function() {
    jQuery(this).closest(".form-fields-field").find(".form-fields-field-form").slideUp("fast").end()
        .fadeTo("medium", "0.4").addClass("form-fields-field-deleted");
    return false;
});
// Field undo delete link
jQuery("a.form-fields-field-undodelete").click(function() {
    jQuery(this).closest(".form-fields-field").fadeTo("medium", "1.0").removeClass("form-fields-field-deleted");
    var $fieldset = jQuery(this).closest(".form-fields-fieldset");
    if ($fieldset.hasClass("form-fields-fieldset-deleted")) {
        $fieldset.fadeTo("medium", "1.0").removeClass("form-fields-fieldset-deleted");
    }
    return false;
});
// Fielset edit link
jQuery("a.form-fields-fieldset-edit").click(function() {
    var $fieldset = jQuery(this).closest(".form-fields-fieldset");
    var $fieldset_form_id = $fieldset.find(".form-fields-fieldset-form").attr("id");
    var $fieldset_form_url = "%7$s" + ("%7$s".indexOf("?", 0) === -1 ? "?" : "&") + "%2$s=" + encodeURIComponent($fieldset_form_id) + "&formfield_id=" + $fieldset.find(".form-fields-fieldset-id").attr("value");
    jQuery.plugg.ajax({
        effect: "slide",
        type: "get",
        target: "#" + $fieldset_form_id,
        dataType: "html",
        url: $fieldset_form_url
    });
    return false;
});
// Fieldset delete link
jQuery("a.form-fields-fieldset-delete").click(function() {
    var $fieldset = jQuery(this).closest(".form-fields-fieldset");
    if ($fieldset.find(".form-fields-fieldset-id").attr("value")) {
        $fieldset.find(".form-fields-fieldset-form").slideUp("fast").end()
            .find(".form-fields-field-delete").click().end()
            .fadeTo("medium", "0.4").addClass("form-fields-fieldset-deleted").find(".form-fields-fieldset-undodelete").click(function() {
                $fieldset.find(".form-fields-field-undodelete").click().end()
                    .fadeTo("medium", "1.0").removeClass("form-fields-fieldset-deleted");
                return false;
            });
    } else {
        $fieldset.remove(); // fieldset not saved yet
    }
    return false;
});
// Fieldset add link
jQuery("#plugg-form-fields-fieldset-add").click(function() {
    var $fieldset = jQuery("#plugg-form-fields-fieldset-template").clone(true).attr("id", "").hide();
    var $fieldset_form = $fieldset.find(".form-fields-fieldset-form");
    $fieldset_form.attr("id", $fieldset_form.attr("id") + Math.floor(Math.random()*100));
    jQuery(".form-fields-fieldsets").append($fieldset);
    $fieldset.slideDown("fast").find(".form-fields-fields").sortable($form_fields_sortable_conf).end()
        .find(".form-fields-fieldset-edit").click();
    return false;
});
// Form submit callback
var $form_is_submitting = false;
jQuery("#%3$s").submit(function() {
    var $form = jQuery(this);
    $form_is_submitting = true;
    // Removed fields/fieldsets marked deleted from the DOM
    $form.find("dt.form-fields-field-deleted, div.form-fields-fieldset-deleted").remove();
    // Any unsaved field or fieldset? If any, confirm the user whether it is ok to proceed without saving.
    var $fields_unsaved = $form.find("dt.form-fields-field-new");
    var $fieldsets_unsaved = $form.find("div.form-fields-fieldset-new");
    if ($fields_unsaved.size() || $fieldsets_unsaved.size()) {
        if (!confirm("%4$s")) {
            $form_is_submitting = false; // reset if not submitting
            return false;
        }
        // Removed unsaved fields/fieldsets from the DOM so their values will not be submitted
        $fields_unsaved.remove();
        $fieldsets_unsaved.remove();
    }
    $form.find(".form-fields-field-form").not(":hidden").hide();
    $form.find(".form-fields-fieldset-form").not(":hidden").hide();
    $form.find("#plugg-form-fields-submit").attr("value", "%8$s").attr("disabled", "disabled");
});
// Alert user when leaving the page if new form fields or form layout have not been saved yet
window.onbeforeunload = function() {
    if ($form_is_submitting) {
        $form_is_submitting = false; // reset
        return;
    }
    
    var $form = jQuery("#%3$s");
    if ($form.find(".form-fields-field-new").not(".form-fields-field-deleted").size() // new fields that have not been saved yet
        || $form.find(".form-fields-fieldset-new").not(".form-fields-fieldset-deleted").size() // new fieldsets that have not been saved yet
    ) {
        return "%5$s";
    } else if ($form.find(".form-fields-field-new-saved").not(".form-fields-field-deleted").size() // new fields that have been saved
        || $form.find(".form-fields-fieldset-new-saved").not(".form-fields-fieldset-deleted").size() // new fieldsets that have been saved
        || $form.find(".form-fields-field-deleted").size() // deleted fields
        || $form.find(".form-fields-fieldset-deleted").size() // deleted fieldsets
    ) {
        return "%6$s";
    }
}
', 
            $editFieldUrl,
            Plugg::PARAM_AJAX,
            $formHtmlId,
            $this->_('One or more fields have not been saved. You must save or delete these fields first before submitting the form.'),
            $this->_('One or more fields have not been saved.'),
            $this->_('You have made changes to the form layout but it has not been saved. You must save the layout for the changes to take effect.'),
            $editFieldsetUrl,
            $this->_('Saving...')
        );
    }

    public function getAdminFieldsCSS()
    {
        return '
.plugg .form-fields {border:none;}
.plugg .form-fields td {border:none; vertical-align:top;}
.plugg .form-fields-title {margin:0; margin-bottom:5px; text-align:center;}
.plugg .form-fields-active {width:320px; text-align:center;}
.plugg .form-fields-fields {list-style:none; min-height:50px; margin:0; padding:0 10px;}
.plugg .form-fields-active .form-fields-fields {background-color:#eee; padding:10px; border-bottom-right-radius:5px; border-bottom-left-radius:5px; -webkit-border-bottom-right-radius:5px; -webkit-border-bottom-left-radius:5px; -moz-border-radius-bottomright:5px; -moz-border-radius-bottomleft:5px; -khtml-border-bottom-right-radius:5px; -khtml-border-bottom-left-radius:5px;}
.plugg .form-fields-available {border:none; padding-left:20px;}
.plugg .form-fields-available .form-fields-fields {width:300px; float:left; padding:0; padding-right:15px;}
.plugg .form-fields-field {margin:0; width:300px; padding:0; line-height:1.4em;}
.plugg .form-fields-fieldset,
.plugg .form-fields-active .form-fields-field {margin-bottom:10px; text-align:left;}
.plugg .form-fields-fieldset-form,
.plugg .form-fields-field-form {background-color:#fbfbfb; margin:0; padding:7px 9px; display:none;}
.plugg .form-fields-fieldset-placeholder,
.plugg .form-fields-field-placeholder {border:1px dashed #999; width:298px; padding:0; margin-bottom:10px;}
.plugg .form-fields-fieldset-placeholder {width:318px;}
.plugg .form-fields-fieldset-label,
.plugg .form-fields-field-label {padding:7px 9px; font-size:12px; line-height:1; font-weight:bold; background-color:#ddd; text-align:left;}
.plugg .form-fields-fieldset-placeholder,
.plugg .form-fields-field-placeholder,
.plugg .form-fields-fieldset-label,
.plugg .form-fields-field-label {border-top-right-radius:5px; border-top-left-radius:5px; -webkit-border-top-right-radius:5px; -webkit-border-top-left-radius:5px; -moz-border-radius-topright:5px; -moz-border-radius-topleft:5px; -khtml-border-top-right-radius:5px; -khtml-border-top-left-radius:5px;}
.plugg .form-fields-fieldset-control a, 
.plugg .form-fields-field-control a {margin-left:3px;}
.plugg .form-fields-fieldset-control,
.plugg .form-fields-field-control {padding:5px; font-size:12px; float:right; height:26px; background-color:transparent;}
.plugg .form-fields-field-control {display:none;}
.plugg .form-fields-fields dd {padding:5px; margin:0; margin-bottom:10px;}
.plugg .form-fields-field-undodelete,
.plugg .form-fields-fieldset-undodelete {display:none;}
.plugg .form-fields-fieldset-deleted .form-fields-fieldset-edit,
.plugg .form-fields-fieldset-deleted .form-fields-fieldset-delete,
.plugg .form-fields-field-deleted .form-fields-field-edit,
.plugg .form-fields-field-deleted .form-fields-field-delete {display:none;}
.plugg .form-fields-fieldset-deleted .form-fields-fieldset-undodelete,
.plugg .form-fields-field-deleted .form-fields-field-undodelete {display:inline;}
#plugg-form-fields-fieldset-template {display:none;}
#plugg-form-fields-fieldset-add {display:block; text-align:right; margin-bottom:10px;}
';
    }
}