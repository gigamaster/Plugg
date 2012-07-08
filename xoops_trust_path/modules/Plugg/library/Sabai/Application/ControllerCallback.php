<?php
require_once 'Sabai/Application/Controller.php';

class Sabai_Application_ControllerCallback extends Sabai_Application_Controller
{
    /**
     * Callback to be called upon execution of controller, usually a string or array.
     * @var mixed
     */
    protected $_callback;
    /**
     * Extra parameters passed to the callback
     * @var array
     */
    protected $_params;

    /**
     * Constructor
     *
     * @param mixed $callback
     * @param array $params
     */
    public function __construct($callback, $params = array())
    {
        $this->_callback = $callback;
        $this->_params = $params;
    }

    /**
     * Executes the controller using the callback
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        call_user_func_array($this->_callback, array_merge(array($request, $response), $this->_params));
    }
}