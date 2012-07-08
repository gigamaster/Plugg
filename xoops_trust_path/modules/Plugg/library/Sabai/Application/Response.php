<?php
abstract class Sabai_Application_Response
{
    private $_application;
    protected $_status = self::VIEW, $_messages = array(), $_content, $_isSending = false;

    const ERROR = 1, SUCCESS = 2, VIEW = 3,
        MESSAGE_ERROR = 1, MESSAGE_WARNING = 2, MESSAGE_INFO = 3, MESSAGE_SUCCESS = 4;
        
    public function setApplication(Sabai_Application $application)
    {
        $this->_application = $application;
    }
    
    /**
     * Call an application helper
     */
    public function __call($name, $args)
    {
        if (!$this->_isSending) {
            throw new Exception('Application helpers may not be called through the response object.');
        }
        
        array_unshift($args, $this->_application);

        return call_user_func_array(array($this->_application->getHelper($name), 'help'), $args);
    }
    
    public function send()
    {
        $this->_isSending = true;
        
        // Send response based on the status
        switch ($this->_status) {
            case self::ERROR:
                return $this->_sendError();
                
            case self::SUCCESS:
                return $this->_sendSuccess();
                
            default:
                return $this->_sendContent();
        }
    }
    
    /**
     * Sends an error response
     */
    abstract protected function _sendError();

    /**
     * Sends a successful response
     */
    abstract protected function _sendSuccess();

    /**
     * Sends a content response
     */
    abstract protected function _sendContent();

    public function setContent($content)
    {
        $this->_content = $content;
        
        return $this;
    }
    
    public function hasContent()
    {
        return isset($this->_content);
    }
    
    public function getContent()
    {
        return $this->_content;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        
        return $this;
    }

    /**
     * Sets the application status as error
     *
     * @param string
     */
    public function setError($msg = null)
    {
        if (isset($msg)) $this->addMessage($msg, self::MESSAGE_ERROR);
        
        return $this->setStatus(self::ERROR);
    }

    /**
     * Sets the application status as success
     *
     * @param string $msg
     */
    public function setSuccess($msg = null)
    {
        if (isset($msg)) $this->addMessage($msg, self::MESSAGE_SUCCESS);
        
        return $this->setStatus(self::SUCCESS);
    }

    /**
     * Checks if the response status is success
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->_status == self::SUCCESS;
    }

    /**
     * Checks if the response status is error
     *
     * @return bool
     */
    public function isError()
    {
        return $this->_status == self::ERROR;
    }

    /**
     * Adds a message
     *
     * @param string $string
     * @param int $level
     */
    public function addMessage($string, $level = self::MESSAGE_INFO)
    {
        $this->_messages[] = array('msg' => $string, 'level' => $level);
        
        return $this;
    }
    
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Clears all messages
     */
    public function clearMessages()
    {
        $this->_messages = array();
        
        return $this;
    }
}