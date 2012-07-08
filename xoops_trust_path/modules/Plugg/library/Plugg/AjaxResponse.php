<?php
class Plugg_AjaxResponse extends Plugg_Response
{
    private $_ajaxTargetId, $_jsonData = array();
    
    public function __construct($ajaxTargetId)
    {
        $this->_ajaxTargetId = $ajaxTargetId;
    }
    
    public function isAjax()
    {
        return $this->_ajaxTargetId;
    }
    
    public function setSuccess($msg = null, $url = array(), array $json = array())
    {
        parent::setSuccess($msg, $url);
        if (!empty($json)) {
            foreach ($json as $k => $v) $this->_jsonData[$k] = $v;
        }
        return $this;
    }
    
    public function setError($msg = null, $url = array(), array $json = array())
    {
        parent::setError($msg, $url);
        if (!empty($json)) {
            foreach ($json as $k => $v) $this->_jsonData[$k] = $v;
        }
        return $this;
    }
    
    protected function _sendError(Sabai_Request $request = null)
    {   
        // Send error response as json
        header('HTTP/1.1 500');
        header(sprintf('Content-type: text/javascript; charset=%s', $this->_charset));
        echo json_encode(array_merge($this->_jsonData, array(
            'message' => $this->_messages,
            'url' => (string)$this->_getResponseUrl($this->_errorUrl)
        )));
    }

    protected function _sendSuccess(Sabai_Request $request = null)
    {   
        // Send success response as json
        header('HTTP/1.1 278 Success'); // 278 status code is used internally by the jQuery plugin
        header(sprintf('Content-type: text/javascript; charset=%s', $this->_charset));
        echo json_encode(array_merge($this->_jsonData, array(
            'message' => $this->_messages,
            'url' => (string)$this->_getResponseUrl($this->_successUrl)
        )));
    }
}