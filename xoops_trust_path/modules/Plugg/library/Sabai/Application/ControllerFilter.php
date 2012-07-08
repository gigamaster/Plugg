<?php
/**
 * Front Controller
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Application
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.2.0
 * @abstract
 */
abstract class Sabai_Application_ControllerFilter
{
    /**
     * Pre execution filter
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param Sabai_Application $application
     */
    abstract public function before(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application);

    /**
     * Post execution filter
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param Sabai_Application $application
     */
    abstract public function after(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application);
}