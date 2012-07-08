<?php
require_once 'Sabai/Application.php';

class Sabai_Application_Web extends Sabai_Application
{
    protected $_url, $_localUrl, $_urlBase = '/', $_urlPrototype,
        $_scripts = array('main' => ''), $_currentScriptName = 'main',
        $_modRewriteFormat, $_previousRequestUrl;

    /**
     * Constructor
     */
    protected function __construct($id, $name, $title, $path, $routeParam, $url)
    {
        parent::__construct($id, $name, $title, $path, $routeParam);
        $this->_url = $this->_localUrl = $url;
    }

    public function run(Sabai_Application_Controller $controller, Sabai_Request $request, Sabai_Application_Response $response = null)
    {
        $this->_previousRequestUrl = $this->hasSessionVar('request_url') ? $this->getSessionVar('request_url') : null;
        
        // Save the requested url into session for later use
        $this->setSessionVar('request_url', $request->getUrl());
        
        if (!isset($response)) $response = new Sabai_Application_WebResponse();
        
        // Add messages save on the previous request to the response content as flash messages
        if ($this->hasSessionVar('flash')) {
            $response->setFlashMessages($this->getSessionVar('flash'));
            $this->unsetSessionVar('flash');
        }
        
        $response = parent::run($controller, $request, $response);
        
        // Save response messages to session if flashing is enabled
        if ($response->isFlashEnabled()) {
            $this->setSessionVar('flash', $response->getMessages());
        }
        
        return $response;
    }
    
    /**
     * Checks whether the supplied URL is a local URL
     *
     * @param mixed $url string or array
     */
    public function isLocalUrl($url)
    {
        if (is_array($url)) {
            if (!array_key_exists('url', $url)) return true;

            $url = $this->createUrl($url);
        }

        return (($url_arr = @parse_url($url))
            && !empty($url_arr['host'])
            && !empty($url_arr['scheme'])
            && strpos($this->_localUrl, $url_arr['scheme'] . '://' . $url_arr['host']) === 0
        );
    }
    
    public function getPreviousRequestUrl()
    {
        return $this->_previousRequestUrl;
    }

    public function getScript($name)
    {
        return $this->_scripts[$name];
    }

    public function setScript($name, $script)
    {
        $this->_scripts[$name] = $script;

        return $this;
    }

    public function setCurrentScriptName($name)
    {
        $this->_currentScriptName = $name;

        return $this;
    }

    public function getCurrentScriptName()
    {
        return $this->_currentScriptName;
    }

    public function getUrl($path = '', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        if (empty($path)) {
            return $this->createUrl(array(
                'params' => $params,
                'fragment' => $fragment,
                'separator' => $separator
            ));
        }

        if (0 === strpos($path, '/')) {
            return $this->createUrl(array(
                'base' => $path,
                'params' => $params,
                'fragment' => $fragment,
                'separator' => $separator
            ));
        }

        return $this->createUrl(array(
            'path' => $path,
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator
        ));
    }

    public function getScriptUrl($scriptName = null)
    {
        if (!isset($script_name)) $script_name = $this->_currentScriptName;

        return $this->_url . '/' . $this->getScript($script_name);
    }

    public function setUrlBase($urlBase)
    {
        $this->_urlBase = $urlBase;

        return $this;
    }

    public function getUrlBase()
    {
        return $this->_urlBase;
    }

    public function setModRewriteFormat($modRewriteFormat, $script)
    {
        $this->_modRewriteFormat[$script] = $modRewriteFormat;

        return $this;
    }

    /**
     * Creates an application URL from an array of options.
     *
     * @param array $options
     * @return string
     */
    public function createUrl(array $options = array())
    {
        $default = array(
            'url' => $this->_url,
            'base' => $this->_urlBase,
            'path' => '',
            'params' => array(),
            'fragment' => '',
            'script' => $this->_currentScriptName,
            'separator' => '&amp;',
            'mod_rewrite' => true,
        );
        
        $options = array_merge($default, $options);
        $script_name = $options['script'];
        if (!isset($this->_urlPrototype[$script_name])) {
            require_once 'Sabai/Application/Url.php';
            $this->_urlPrototype[$script_name] = new Sabai_Application_Url($this->_routeParam);
            if (isset($this->_modRewriteFormat[$script_name])) {
                $this->_urlPrototype[$script_name]->setModRewriteFormat($this->_modRewriteFormat[$script_name]);
            }
        }
        
        $url = clone $this->_urlPrototype[$script_name];
        $url->setData(
            $options['url'],
            $options['base'],
            $options['path'],
            $options['params'],
            $options['fragment'],
            $this->_scripts[$script_name],
            $options['separator']
        );
        if (!$options['mod_rewrite']) $url->setModRewriteFormat(null);

        return $url;
    }
}