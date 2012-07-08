<?php
class Plugg_HTMLPurifier_Plugin extends Plugg_Plugin
{
    public function onPluggInit()
    {
        // Add the HTMLPurifier service
        $this->_application->getLocator()->addProviderClass(
            'HTMLPurifier',
            array('HTMLPurifierConfig' => new stdClass),
            'HTMLPurifier',
            array('HTMLPurifier.auto.php', 'HTMLPurifier.php')
        );

        // Add the HTMLPurifierConfig service
        $this->_application->getLocator()->addProviderFactoryMethod(
            'HTMLPurifierConfig',
            array('Plugg_HTMLPurifier_ConfigFactory', 'create'),
            array(
                'options' => array(
                    'Cache.SerializerPath' => ($path = $this->getConfig('cacheSerializerPath')) ? $path : $this->_path . '/cache',
                    'HTML.DefinitionID' => $this->name,
                    'Attr.EnableID' => true,
                    'URI.DisableExternalResources' => $this->getConfig('uriDisableExternalResources') ? true : false,
                    'AutoFormat.RemoveEmpty' => true,
                    'AutoFormat.Linkify' => $this->getConfig('autoFormatLinkify') ? true : false,
                    'AutoFormat.AutoParagraph' => $this->getConfig('autoFormatAutoParagraph') ? true : false,
                    'HTML.AllowedElements' => $this->getConfig('htmlAllowedElements'),
                )
            ),
            $this->_path . '/ConfigFactory.php'
        );
    }

    public function onFormBuildSystemAdminSettings($form)
    {
        $form[$this->_name] = array(
            '#type' => 'fieldset',
            '#collapsible' => true,
            '#tree' => true,
            '#title' => $this->_('HTMLPurifier library settings'),
            '#description' => $this->_('Some plugins use the HTMLPurifier library to filter HTML contents. Here you can configure the default settings of the library.'),
            'cacheSerializerPath' => array(
                '#title' => $this->_('Cache directory'),
                '#description' => sprintf($this->_('Enter the fullpath to a directory where HTMLPurifier cache files are stored. The path must be writable by the webserver. Leave it blank to use the default directory: %s'), $this->_path . '/cache'),
                '#weight' => 5,
                '#collapsible' => true,
                '#collapsed' => true,
                '#default_value' => $this->getConfig('cacheSerializerPath'),
            ),
            'uriDisableExternalResources' => array(
                '#title' => $this->_('Disable external resources.'),
                '#description' => $this->_('Check this option to disable the embedding of external resources, preventing users from embedding things like images from other hosts.'),
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('uriDisableExternalResources'),
            ),
            'autoFormatLinkify' => array(
                '#title' => $this->_('Enable auto-linkify.'),
                '#description' => $this->_('Check this option to automagically convert URLs in user posts to HTML links.'),
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('autoFormatLinkify'),
            ),
            'autoFormatAutoParagraph' => array(
                '#title' => $this->_('Enable auto-paragraph.'),
                '#description' => $this->_('Check this option to convert double newlines in user posts to paragraphs. p tags must be allowed for this directive to take effect. We do not use br tags for paragraphing, as that is semantically incorrect.'),
                '#default_value' => $this->getConfig('autoFormatAutoParagraph'),
                '#type' => 'checkbox',
            ),
            'htmlAllowedElements' => array(
                '#title' => $this->_('Allowed HTML tags'),
                '#description' => $this->_('HTML tags allowed to be used. Separate tags with a comma. If you are not sure what to enter here, it is recommended that you leave this option as-is.'),
                '#default_value' => $this->getConfig('htmlAllowedElements'),
                '#type' => 'textmulti',
                '#separator' => ',',
                '#rows' => 3,
            )
        );

        // Add callback called upon sumission of the form
        $form['#submit'][] = array($this, 'submitSystemAdminSettings');
    }

    public function submitSystemAdminSettings($form)
    {
        if (!empty($form->values[$this->_name])) {
            $this->saveConfig($form->values[$this->_name], array(), false);
        }
    }
    
    public function getDefaultConfig()
    {
        return array(
            'cacheSerializerPath' => '',
            'uriDisableExternalResources' => true,
            'autoFormatLinkify' => true,
            'autoFormatAutoParagraph' => true,
            'htmlAllowedElements' => array('a', 'abbr', 'acronym', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'dd', 'del', 'dfn', 'div', 'dl',
                 'dt', 'em', 'i', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 's', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot',
                'th', 'thead', 'tr', 'tt', 'u', 'ul','var'
            ),
        );
    }
}