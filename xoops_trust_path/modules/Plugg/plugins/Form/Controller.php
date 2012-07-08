<?php
abstract class Plugg_Form_Controller extends Sabai_Application_Controller
{
    protected $_submitable = true, $_submitButtonName = '_form_submit', $_submitButtonLabel,
        $_successUrl, $_cancelUrl = array(),
        $_ajaxSubmit = true, $_ajaxCancelType, $_ajaxCancelUrl = array(),
        $_ajaxOnSuccess, $_ajaxOnSuccessUrl, $_ajaxOnSuccessRedirect = true,
        $_ajaxOnError, $_ajaxOnErrorUrl, $_ajaxOnErrorRedirect = true,
        $_freeze = false;

    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Initialize form storage bin
        $form_storage = array();
        // Check if form build ID has been sent in the request
        if ($form_build_id = $request->asStr('_form_build_id', null)) {
            // If no previous storage data is found for the requested form build ID, then this could
            // be a hacking attempt so terminate the script without doing anything.
            if (!$this->getPlugin('Form')->hasSessionVar($form_build_id)) {
                $response->setError($this->_('Invalid form build ID:' . $form_build_id));
                
                return;
            }
            
            // Load previously stored data of the form from the session
            if (false === $form_storage = @unserialize($this->getPlugin('Form')->getSessionVar($form_build_id))) {
                $form_storage = array();
            }
        }
        
        if (!$form_settings = $this->_getFormSettings($request, $response, $form_build_id, $form_storage)) {
            // Set error message if not set
            if (!$response->isError()) $response->setError($this->_('Invalid request'));

            return;
        }

        if (($ajax_param = $request->isAjax()) && $ajax_param != 'plugg-content') {
            if ($ajax_trigger_element = $request->isAjax(Plugg::PARAM_AJAX . '_element')) {
                // Ajax request was triggered by one of the form elements.
                if (!$ajax_callback = $this->_triggerElementAjax($ajax_trigger_element, $form_settings)) {
                    // Invalid ajax element request
                    $response->setError($this->_('Invalid request'));
                        
                    return;
                }

                $response->setContent(Plugg::processCallback($ajax_callback));
                
                return;
            }
        }

        // Build the form
        if (isset($form_settings['#method']) && strtolower($form_settings['#method']) === 'get') {
            // GET form method need to embed requested route as hidden value to be passed in the next request
            $form_settings[$this->getRouteParam()] = array(
                '#type' => 'hidden',
                '#value' => $this->getRequestedRoute(),
            );
        }
        $form = $this->getPlugin('Form')->buildForm($form_settings, true, $request->getParams());

        // Validate form and submit
        if ($this->_submitable) {            
            if ($res = $this->getPlugin('Form')->submitForm($form)) {
                if (!$response->isSuccess() && !$response->hasContent()) {
                    $response->setSuccess(
                        $this->_('Data updated successfully.'),
                        isset($this->_successUrl) ? $this->_successUrl : $request->getUrl()
                    );
                }

                // Clear flash messages if request is ajax and redirection disabled
                if ($ajax_param
                    && (isset($this->_ajaxOnSuccessUrl)
                        || !$this->_ajaxOnSuccessRedirect
                        || isset($this->_ajaxOnErrorUrl)
                        || !$this->_ajaxOnErrorRedirect)
                ) {
                    $response->setFlashEnabled(false);
                }
                
                if (!empty($form->settings['#enable_storage'])) {
                    // Clear form storage from the session
                    $this->getPlugin('Form')->unsetSessionVar($form->settings['#build_id']);
                }

                return;
            }

            // If error is set, clear form storage from the session and do not display the form
            if ($response->isError()) {
                if (!empty($form->settings['#enable_storage'])) {
                    $this->getPlugin('Form')->unsetSessionVar($form->settings['#build_id']);
                }
                
                return;
            }
 
            // Rebuild form?
            if ($form->rebuild) {
                $form->settings = $this->_getFormSettings($request, $response, $form->settings['#build_id'], $form->storage);
            }
        }
 
        // Render
        $form_html = $this->getPlugin('Form')->renderForm($form, false, $this->_freeze);
        if ($ajax_param && !$request->asBool('_noscript')) {
            $form_script = $this->_getFormScript($request, $ajax_param);
        } else {
            $form_script = '';
        }
        
        $response->setContent($form_html . PHP_EOL . $form_script);
    }
    
    protected function _getFormSettings(Sabai_Request $request, Sabai_Application_Response $response, $formBuildId, array &$formStorage)
    {
        // Load the form settings
        if (false === $form_settings = $this->_doGetFormSettings($request, $response, $formStorage)) {
            return false;
        }
        
        // Make sure an array is returned by the _getForm() method
        if (!is_array($form_settings)) {
            throw new Plugg_Exception(sprintf('%s::_getForm() must return an array', get_class($this)));   
        }

        // Auto define form ID if not alreaady set
        if (!isset($form_settings['#id']) || strlen($form_settings['#id']) === 0) {
            // Use the class name as the form ID, but replace the long Plugg_XXX_Controller prefix with XXX,
            // where XXX stands for the name of current running plugin
            $form_settings['#id'] = strtolower(str_replace(
                'Plugg_' . $this->getPlugin()->name . '_Controller',
                $this->getPlugin()->name,
                get_class($this)
            ));
        }

        // Initialize some required form properties
        $form_settings['#build_id'] = $formBuildId;
        $form_settings['#initial_storage'] = $formStorage;
        if (!isset($form_settings['#action'])) $form_settings['#action'] = $request->getUrl();
        $form_settings['#post_render'][] = array(array($this, 'viewForm'), array($request, $response));
        
        // Always use token unless explicitly disabled
        if (!isset($form_settings['#token'])) $form_settings['#token'] = true;

        // Set the default Ajax onSuccess/onError functions if not set and Ajax redirect is disabled
        if (!isset($this->_ajaxOnSuccess) && (!$this->_ajaxOnSuccessRedirect || isset($this->_ajaxOnSuccessUrl))) {
            $this->_ajaxOnSuccess = "function(xhr, result, target){jQuery.each(result.message, function(k, v){jQuery(\"#plugg-flash\").prepend(\"<div class='plugg-success fadeout'>\" + v.msg + \"</div>\");});}";
        }
        if (!isset($this->_ajaxOnError) && (!$this->_ajaxOnErrorRedirect || isset($this->_ajaxOnErrorUrl))) {    
            $this->_ajaxOnError= "function(xhr, result, target){jQuery.each(result.message, function(k, v){jQuery(\"#plugg-flash\").prepend(\"<div class='plugg-error'>\" + v.msg + \"</div>\");});}";
        }

        // Create form cancel link
        $cancel_link = null;
        if (($ajax_param = $request->isAjax()) && $ajax_param != 'plugg-content') {
            // Create cancel link that will close the form
            if (isset($this->_cancelUrl)) $cancel_link = $this->_getAjaxCancelLink($ajax_param);
        } elseif (isset($this->_cancelUrl)) {
            $cancel_link = sprintf(
                $this->_(' or <a href="%s">cancel</a>'),
                $this->_cancelUrl instanceof Sabai_Application_Url ? $this->_cancelUrl : $this->createUrl($this->_cancelUrl)
            );
        }

        // Add submit button and cancel link
        if (!empty($this->_submitButtonName)) {
            $form_settings[$this->_submitButtonName]['#tree'] = true;
            $form_settings[$this->_submitButtonName]['#separator'] = '<span class="separator"> </span>';
            $form_settings[$this->_submitButtonName]['#weight'] = 99;
            $form_settings[$this->_submitButtonName]['#class'] = 'plugg-form-buttons';
            $form_settings[$this->_submitButtonName]['submit'] = array(
                '#type' => 'submit',
                '#value' => isset($this->_submitButtonLabel) ? $this->_submitButtonLabel : $this->_('Submit'),
                '#validate' => array(array(array($this, 'validateForm'), array($request, $response))),
                '#submit' => array(array(array($this, 'submitForm'), array($request, $response))),
                '#disabled' => !$this->_submitable,
            );
            if (isset($cancel_link)) {
                $form_settings[$this->_submitButtonName]['cancel'] = array(
                    '#type' => 'markup',
                    '#markup' => $cancel_link,
                    '#weight' => 99,
                );
            }
        }

        return $form_settings;
    }
    
    private function _getAjaxCancelLink($ajaxParam)
    {
        // Create cancel link that will close the form only when the form is requested as partial content
        switch ($this->_ajaxCancelType) {
            case 'slide':
                return sprintf($this->_(' or %s'), sprintf(
                    "<a href=\"#%1\$s\" onclick=\"jQuery('#%1\$s').slideUp('fast'); return false\">%2\$s</a>",
                    h($ajaxParam), $this->_('cancel')
                ));
            case 'remote':
                return sprintf($this->_(' or %s'), $this->LinkToRemote(
                    $this->_('cancel'),
                    h($ajaxParam),
                    $this->_cancelUrl,
                    $this->_ajaxCancelUrl,
                    array()
                ));
            case 'none':
                return ;
            default:
                return sprintf($this->_(' or %s'), sprintf(
                    "<a href=\"#%1\$s\" onclick=\"jQuery('#%1\$s').hide(); return false\">%2\$s</a>",
                    h($ajaxParam), $this->_('cancel')
                ));
        }
    }

    private function _getFormScript($request, $ajaxTarget)
    {
        $ajax_onsuccess_url = $ajax_onerror_url = '';
        if (isset($this->_ajaxOnSuccessUrl)) {
            $this->_ajaxOnSuccessUrl['separator'] = '&';
            $this->_ajaxOnSuccessUrl['params'] = array_merge(
                (array)@$this->_ajaxOnSuccessUrl['params'],
                array(Plugg::PARAM_AJAX => $ajaxTarget)
            );
            $ajax_onsuccess_url = $this->_ajaxOnSuccessUrl instanceof Sabai_Application_Url ? $this->_ajaxOnSuccessUrl : $this->createUrl($this->_ajaxOnSuccessUrl);
        }
        if (isset($this->_ajaxOnErrorUrl)) {
            $this->_ajaxOnErrorUrl['separator'] = '&';
            $this->_ajaxOnErrorUrl['params'] = array_merge(
                (array)@$this->_ajaxOnErrorUrl['params'],
                array(Plugg::PARAM_AJAX => $ajaxTarget)
            );
            $ajax_onerror_url = $this->_ajaxOnErrorUrl instanceof Sabai_Application_Url ? $this->_ajaxOnErrorUrl : $this->createUrl($this->_ajaxOnErrorUrl);
        }
        return sprintf('
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery("#%1$s form input[type=submit]").click(function(){
        $this = jQuery(this);
        $form = $this.closest("form");

        // Disable the clicked button and then add its value as hidden value
        $form.append($this.clone().attr("type", "hidden"));
        $this.attr("value", "%7$s").attr("disabled", "disabled");

        var submit = %8$s;
        if (submit) {
            // Uploading file via ajax is not supported.
            $form.find("input[type=file]").each(function(){
                if (jQuery(this).attr("value")) {
                    submit = false;
                    return false;
                }
            });
        }
        if (!submit) return true;

        jQuery.plugg.ajax({
            type:"post",
            target:"#%1$s",
            url:"%2$s",
            onSuccess:%3$s,
            onSuccessUrl:"%4$s",
            onSuccessRedirect:%5$s,
            onError:%9$s,
            onErrorUrl:"%10$s",
            onErrorRedirect:%11$s,
            // jQuery serialize() does not include data of submit buttons
            data:"%6$s=" + encodeURIComponent("%1$s") + "&" + encodeURIComponent($this.attr("name")) + "=" + encodeURIComponent($this.attr("value")) + "&" + $form.serialize()
        });

        return false;
    });
});
</script>',
            h($ajaxTarget),
            $request->getUrl(),
            isset($this->_ajaxOnSuccess) ? $this->_ajaxOnSuccess : 'null',
            $ajax_onsuccess_url,
            $this->_ajaxOnSuccessRedirect ? 'true' : 'false',
            Plugg::PARAM_AJAX,
            $this->_('Please wait...'),
            $this->_ajaxSubmit ? 'true' : 'false',
            isset($this->_ajaxOnError) ? $this->_ajaxOnError : 'null',
            $ajax_onerror_url,
            $this->_ajaxOnErrorRedirect ? 'true' : 'false'
        );
    }
    
    private function _triggerElementAjax($elementNames, $formSettings)
    {   
        // Ajax request was triggered by one of the form elements.
        $ajax_trigger = $formSettings;
        foreach (explode(',', $elementNames) as $key) {
            if (!isset($ajax_trigger[$key])) return false;

            $ajax_trigger = $ajax_trigger[$key];
        }
        
        return isset($ajax_trigger['#ajax']['callback']) ? $ajax_trigger['#ajax']['callback'] : false;
    }

    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        return true;
    }
    
    public function viewForm(Plugg_Form_Form $form, &$formHtml, Sabai_Request $request, Sabai_Application_Response $response)
    {
    }
    
    abstract protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage);
}