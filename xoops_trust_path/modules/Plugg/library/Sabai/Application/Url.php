<?php
class Sabai_Application_Url implements ArrayAccess
{
    private $_routeParam;
    private $_modRewriteFormat;
    private $_data = array();

    public function __construct($routeParam)
    {
        $this->_routeParam = $routeParam;
    }
    
    public function setModRewriteFormat($modRewriteFormat)
    {
        $this->_modRewriteFormat = $modRewriteFormat;
    }

    public function setData($url, $base, $path, array $params, $fragment, $script, $separator)
    {
        $this->_data = array(
          'url' => $url,
          'base' => $base,
          'path' => $path,
          'params' => $params,
          'fragment' => $fragment,
          'script' => $script,
          'separator' => $separator
        );
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __toString()
    {
        $url = $this->_data['url'] . '/' . $this->_data['script'];
        if (('/' !== $route = rtrim($this->_data['base'], '/') . '/' . $this->_data['path'])
            && (!isset($this->_modRewriteFormat))
        ) {
            $params = array_merge($this->_data['params'], array($this->_routeParam => $route));
        } else {
            $params = $this->_data['params'];
        }
        if ($query_str = http_build_query($params, null, $this->_data['separator'])) {
            $query = '?' . $query_str;
        } else {
            $query = '';
        }
        if (isset($this->_modRewriteFormat)) {
            $url = sprintf($this->_modRewriteFormat, $route, $query_str, $query);
        } else {
            $url .= strpos($url, '?') ? $query_str : $query;
        }

        return !empty($this->_data['fragment']) ? $url . '#' . urlencode($this->_data['fragment']) : $url;
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }
}