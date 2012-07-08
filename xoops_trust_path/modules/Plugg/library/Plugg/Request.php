<?php
class Plugg_Request extends Sabai_Request_Http
{
    protected $_url;
    
    public function isAjax($ajaxRequestParam = null)
    {
        if (!isset($ajaxRequestParam)) $ajaxRequestParam = Plugg::PARAM_AJAX;
        
        if (!$this->isPost()) return $this->asStr($ajaxRequestParam, false);

        // Ajax request parameter must be set in $_POST when making POST requests.
        // This is because forms that were requested via AJAX will mostly submit itself to a URL
        // with the ajax request parameter appended and that will result in an AJAX page
        // rendered on the screen if we used $_GET/$_REQUEST or the request object.
        return !empty($_POST[$ajaxRequestParam]) ? $_POST[$ajaxRequestParam] : false;
    }

    public function getUrl()
    {
        if (isset($this->_url)) return $this->_url;
        
        $request_url = parent::getUrl();

        // Remove Ajax parameters from the URI

        // Return original if ajax parameter is not set
        if (!$this->asStr(Plugg::PARAM_AJAX, false) || (!$parsed = @parse_url($request_url))) {
            $this->_url = $request_url;
            
            return $this->_url;
        }

        $params = array();
        if (!empty($parsed['query']) && ($queries = explode('&', $parsed['query']))) {
            foreach (array_keys($queries) as $i) {
                if ($query = explode('=', $queries[$i])) {
                    if (!in_array($query[0], array(Plugg::PARAM_AJAX))) {
                        $params[$query[0]] = rawurldecode($query[1]);
                    }
                }
            }
        }

        $this->_url = sprintf(
            '%s://%s%s?%s%s',
            $parsed['scheme'],
            !empty($parsed['port']) ? $parsed['host'] . ':' . $parsed['port'] : $parsed['host'],
            $parsed['path'],
            http_build_query($params),
            !empty($parsed['fragment']) ? '#' . $parsed['fragment'] : ''
        );
        
        return $this->_url;
    }
}