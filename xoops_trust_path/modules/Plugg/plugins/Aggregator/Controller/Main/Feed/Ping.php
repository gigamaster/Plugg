<?php
class Plugg_Aggregator_Controller_Main_Feed_Ping extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$request->isPost()) $this->_sendResponse($response, 'Invalid request method.', true);

        // Validate data received
        $data = isset($_SERVER['HTTP_RAW_POST_DATA']) ? $_SERVER['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
        if (empty($data) || (!$xml = simplexml_load_string($data))) {
            $this->_sendResponse($response, 'Invalid post data.', true);
        }

        // Make sure it is sending xmlrpc ping
        if (!in_array($xml->methodName, array('weblogUpdates.ping', 'weblogUpdates.extendedPing'))) {
            $this->_sendResponse($response, 'Invalid method.', true);
        }

        // Make sure a valid feed is requested
        if ((!$feed_id = $request->asInt('feed_id')) ||
            (!$feed = $this->getPluginModel()->Feed->fetchById($feed_id))
        ) {
            $this->_sendResponse($response, 'Invalid feed.', true);
        }

        // Finally, update the feed's last pinged timestamp
        $feed->last_ping = time();
        if (!$feed->commit()) {
            $this->_sendResponse($response, 'Internal server error.', true);
        }

        $this->_sendResponse($response, 'Thanks for the ping.');
    }

    private function _sendResponse(Sabai_Application_Response $response, $msg, $error = false)
    {
        $tpl = '<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value>
        <struct>
          <member>
            <name>flerror</name>
            <value>
              <boolean>%d</boolean>
            </value>
          </member>
          <member>
            <name>message</name>
              <value>%s</value>
          </member>
        </struct>
      </value>
    </param>
  </params>
</methodResponse>';

        // Create payload
        $payload = sprintf($tpl, $error, h($msg));
        //$payload = mb_convert_encoding(sprintf($tpl, $error, h($msg)), 'UTF-8', SABAI_CHARSET);
        
        $response->setContent($payload)->setLayoutEnabled(false)->setNavigationEnabled(false)
            ->setContentType('text/xml')->setHeader('Content-Length', strlen($payload))->send();
            
        exit;
    }
}