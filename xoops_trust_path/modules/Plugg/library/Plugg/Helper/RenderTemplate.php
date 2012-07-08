<?php
class Plugg_Helper_RenderTemplate extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $templateName, array $vars = null, $pluginName = null)
    {
        if (!$plugin = $application->getPlugin($pluginName)) {
            throw new Plugg_Exception(sprintf('An error occurred while rendering template %s. Plugin %s does not exist.', $templateName, $pluginName));
        }
        
        if (!preg_match('/^([a-z]+)([a-z0-9_]*)$/', $templateName)) {
            throw new Plugg_Exception('Template name can contain only lowercase alphanumeric characters and/or underscores.');
        }
        
        // Append data that have been used in the application 
        $vars = isset($vars) ? array_merge($application->getData(), $vars) : $application->getData();
        
        // Notify plugins that a template is being rendered
        $event_name = implode('', array_map('ucfirst', explode('_', $templateName))) . 'RenderTemplate';
        $application->DispatchEvent($event_name, array($templateName, $vars));
        
        // Create view and then render
        $view = new Sabai_Application_TemplateView($application, $templateName);
        
        return $view->addTemplateDir($application->getPath() . '/templates') // global template directory
            ->addTemplateDir($plugin->path . '/templates') // plugin template directory
            ->assign($vars)
            ->render();
    }
}