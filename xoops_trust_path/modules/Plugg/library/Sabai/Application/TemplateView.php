<?php
class Sabai_Application_TemplateView extends Sabai_Application_View
{
    protected $_templateName, $_vars = array(), $_templateDir = array(), $_templateFileEx = '.tpl', $_templatePaths = array();

    /**
     * Constructor
     * @param $application Sabai_Application
     * @param $templateName string
     */
    public function __construct(Sabai_Application $application, $templateName)
    {
        parent::__construct($application);
        $this->_templateName = $templateName;
    }
    
    public function __isset($name)
    {
        return isset($this->_vars[$name]);
    }

    public function __unset($name)
    {
        unset($this->_vars[$name]);
    }

    public function __set($name, $value)
    {
        $this->_vars[$name] = $value;
    }
    
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->_vars[$k] = $v;
            }
        } else {
            $this->_vars[$name] = $value;
        }
        
        return $this;
    }
    
    public function render()
    {
        ob_start();
        $this->display();
        
        return ob_get_clean();
    }
    
    public function display()
    {
        $this->_includeTemplate($this->_templateName, $this->_vars);
    }

    protected function _includeTemplate($_templateName_, array $_vars_ = array())
    {
        if (!$_template_path_ = $this->getTemplatePath($_templateName_)) {
            throw new Exception('Template file does not exist.');
        }
        
        extract($_vars_, EXTR_SKIP);
        include $this->getTemplatePath($_templateName_);
    }

    public function addTemplateDir($templateDir, $priority = 0)
    {
        if (!isset($this->_templateDir[$priority])) {
            $this->_templateDir[$priority] = array($templateDir);
        } else {
            array_unshift($this->_templateDir[$priority], $templateDir);
        }
        krsort($this->_templateDir, SORT_NUMERIC);

        return $this;
    }

    public function getTemplatePath($templateName)
    {
        // Resolve template path if not yet cached
        if (!isset($this->_templatePaths[$templateName])) {
            $this->_templatePaths[$templateName] = $this->_getTemplatePath($templateName);
        }

        return $this->_templatePaths[$templateName];
    }

    protected function _getTemplatePath($templateName)
    {
        $file = $templateName . $this->_templateFileEx;
        foreach (array_keys($this->_templateDir) as $i) {
            foreach ($this->_templateDir[$i] as $template_dir) {
                $path = $template_dir . '/' . $file;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return false;
    }

    public function setTemplateFileExtension($extension)
    {
        $this->_templateFileEx = $extension;
        
        return $this;
    }
}