<?php
require_once 'Sabai/Application/Response.php';

class Sabai_Application_WebResponse extends Sabai_Application_Response
{
    protected $_headers = array(), $_charset = SABAI_CHARSET, $_contentType = 'text/html', $_returnContent = false,
        $_js, $_jsHead = array(), $_jsHeadAjax, $_jsFoot = array(), $_jsFiles = array(), $_jsIndex = -1,
        $_css = array(), $_cssFiles = array(), $_cssIndex = -1, $_cssFileIndices = array(),
        $_htmlHead = array(), $_htmlHeadTitle,
        $_flashMessages = array(), $_flashEnabled = true,
        $_errorUrl = null, $_successUrl = null,
        $_layoutUrl = './layouts/default', $_layoutDir = './layouts/default', $_layoutFile = 'main', $_layoutFileEx = '.html', $_layoutEnabled = true;
    
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        
        return $this;
    }
        
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
        
        return $this;
    }
    
    public function getContentType()
    {
        return $this->_contentType;
    }
    
    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        
        return $this;
    }
    
    public function hasHeader($name)
    {
        return isset($this->_headers[$name]);
    }
    
    public function setError($msg = null, $url = array())
    {
        $this->_errorUrl = $url;
        
        return parent::setError($msg);
    }

    public function setSuccess($msg = null, $url = array())
    {
        $this->_successUrl = $url;
        
        return parent::setSuccess($msg);
    }
    
    public function setLayoutUrl($layoutUrl)
    {
        $this->_layoutUrl = $layoutUrl;

        return $this;
    }

    public function setLayoutDir($layoutDir)
    {
        $this->_layoutDir = $layoutDir;

        return $this;
    }
    
    public function setLayoutFile($layoutFile)
    {
        $this->_layoutFile = $layoutFile;

        return $this;
    }
    
    public function setLayoutEnabled($flag = true)
    {
        $this->_layoutEnabled = (bool)$flag;
        
        return $this;
    }

    public function setLayoutFileExtension($extension)
    {
        $this->_layoutFileEx = $extension;
    }
    
    public function setReturnContent($flag = true)
    {
        $this->_returnContent = (bool)$flag;
        
        return $this;
    }
    
    public function addHtmlHead($head)
    {
        $this->_htmlHead[] = $head;
        
        return $this;
    }

    public function setHtmlHeadTitle($title)
    {
        $this->_htmlHeadTitle = $title;
        
        return $this;
    }

    public function addJsHead($js)
    {
        $this->_jsHead[++$this->_jsIndex] = $js;
        
        return $this;
    }

    public function addJsHeadAjax($js)
    {
        $this->_jsHeadAjax[++$this->_jsIndex] = $js;
        
        return $this;
    }

    public function addJsFoot($js)
    {
        $this->_jsFoot[++$this->_jsIndex] = $js;
        
        return $this;
    }

    public function addJsFile($path, $foot = false, $expression = null)
    {
        $this->_jsFiles[$foot ? 'foot' : 'head'][++$this->_jsIndex] = array($path, $expression);
        
        return $this;
    }

    public function addJs($js)
    {
        $this->_js[++$this->_jsIndex] = $js;
        
        return $this;
    }

    public function addCss($css, $index = null)
    {
        $index = empty($index) ? ++$this->_cssIndex : $index;
        $this->_css[$index] = $css;
        
        return $this;
    }

    public function addCssFile($path, $media = 'screen', $id = null, $index = null)
    {
        $index = empty($index) ? ++$this->_cssIndex : $index;
        // Use id to prevent duplicates
        if (isset($id)) {
            if ($id_index = @$this->_cssFileIndices[$id]) {
                unset($this->_cssFiles[$id_index]);
            }
            $this->_cssFileIndices[$id] = $index;
        }
        $this->_cssFiles[$index] = array($path, $media);
        
        return $this;
    }
    
    public function setFlashMessages(array $flashMessages)
    {
        $this->_flashMessages = $flashMessages;
    }
    
    public function setFlashEnabled($flag = true)
    {
        $this->_flashEnabled = (bool)$flag;
        
        return $this;
    }
    
    public function isFlashEnabled($flag = true)
    {
        return $this->_flashEnabled;
    }

    protected function _sendError()
    {
        $this->_redirect($this->_getResponseUrl($this->_errorUrl));
    }

    protected function _sendSuccess()
    {
        $this->_redirect($this->_getResponseUrl($this->_successUrl));
    }
    
    protected function _redirect($url)
    {
        header('Location: ' . str_replace(array("\r", "\n"), '', $url));
    }
    
    protected function _getResponseUrl($url, $separator = '&')
    {   
        if (!is_null($url)) {
            if (is_array($url)) {
                $url['separator'] = $separator;

                return $this->Url($url);
            }
            if ($url instanceof Sabai_Application_Url) {
                $url['separator'] = $separator;

                return $url;
            }

            return $url;
        }

        return $this->Url(array('base' => '/'));
    }

    protected function _sendContent()
    {   
        // Send headers
        if (!headers_sent()) {
            header(sprintf('Content-Type: %s; charset=%s', $this->_contentType, $this->_charset));
            header('Expires: -1');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            foreach ($this->_headers as $header_name => $header_value) {
                header(str_replace(array("\r", "\n"), '', $header_name . ': ' . $header_value));
            }
        }

        echo $this->_renderContent($this->_content);
    }
    
    public function render()
    {
        $this->_isSending = true; // allow calling helpers
        
        return $this->_renderContent($this->_content);
    }

    protected function _renderContent($content)
    {
        if (!$this->_layoutEnabled) return $content;

        $vars = array(
            'CONTENT' => $content,
            'HTML_HEAD' => implode(PHP_EOL, $this->_htmlHead),
            'HTML_HEAD_TITLE' => isset($this->_htmlHeadTitle) ? $this->_htmlHeadTitle : '',
            'CSS' => $this->_getCSSHTML(),
            'FLASH' => $this->_flashMessages,
        );
        list($vars['JS_HEAD'], $vars['JS_FOOT']) = $this->_getJSHTML();

        $layout_file = $this->_layoutDir . '/' . $this->_layoutFile . $this->_layoutFileEx;

        ob_start();
        $this->_include($layout_file, $vars);
        
        return ob_get_clean();
    }
    
    protected function _include($_file_, array $_vars_)
    {   
        extract($_vars_, EXTR_SKIP);
        include $_file_;
    }

    protected function _getJSHTML()
    {
        $html = array('head' => array(), 'foot' => array());
        foreach (array_keys($this->_jsFiles) as $js_where) {
            foreach (array_keys($this->_jsFiles[$js_where]) as $i) {
                if (isset($this->_jsFiles[$js_where][$i][1])) {
                    // Conditional expression set
                    $html[$js_where][$i] = sprintf(
                        '<!--[if %s]><script type="text/javascript" src="%s"></script><![endif]-->',
                        $this->_jsFiles[$js_where][$i][1],
                        $this->_jsFiles[$js_where][$i][0]
                    );
                } else {
                    $html[$js_where][$i] = sprintf(
                        '<script type="text/javascript" src="%s"></script>',
                        $this->_jsFiles[$js_where][$i][0]
                    );
                }
            }
        }
        foreach (array_keys($this->_jsHead) as $i) {
            $html['head'][$i] = implode(PHP_EOL, array('<script type="text/javascript">', '//<![CDATA[', $this->_jsHead[$i], '//]]>', '</script>'));
        }
        foreach (array_keys($this->_jsFoot) as $i) {
            $html['foot'][$i] = implode(PHP_EOL, array('<script type="text/javascript">', '//<![CDATA[', $this->_jsFoot[$i], '//]]>', '</script>'));
        }
        $_js = array('<script type="text/javascript">', '//<![CDATA[');
        if (!empty($this->_jsHeadAjax)) {
            ksort($this->_jsHeadAjax);
            $_js[] = 'jQuery(document).ajaxComplete(function (evt, request, settings) {';
            foreach (array_keys($this->_jsHeadAjax) as $i) {
                $_js[] = $this->_jsHeadAjax[$i];
            }
            $_js[] = '});';
        }
        if (!empty($this->_js)) {
            ksort($this->_js);
            $_js[] = 'jQuery(document).ready(function() {';
            foreach (array_keys($this->_js) as $i) {
                $_js[] = $this->_js[$i];
            }
            $_js[] = '});';
        }
        $_js[] = '//]]>';
        $_js[] = '</script>';
        $html['head'][$this->_jsIndex + 1] = implode(PHP_EOL, $_js);
        $html_head = implode(PHP_EOL, $html['head']);
        $html_foot = implode(PHP_EOL, $html['foot']);

        return array($html_head, $html_foot);
    }

    protected function _getCSSHTML()
    {
        $html = array();
        foreach (array_keys($this->_cssFiles) as $i) {
            $html[$i] = sprintf('<link rel="stylesheet" type="text/css" media="%s" href="%s" />', $this->_cssFiles[$i][1], $this->_cssFiles[$i][0]);
        }
        foreach (array_keys($this->_css) as $i) {
            $html[$i] = implode(PHP_EOL, array('<style type="text/css"><!--', $this->_css[$i], '--></style>'));
        }
        ksort($html);
        
        // Add layout css file
        array_unshift($html, sprintf('<link rel="stylesheet" type="text/css" media="screen" href="%s/css/%s.css" />', $this->_layoutUrl, $this->_layoutFile));

        return implode(PHP_EOL, $html);
    }
}