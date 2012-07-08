<?php
class Plugg_ImageTransform_Plugin extends Plugg_Plugin
{
    public function onPluggInit()
    {
        // Add the ImageTransform service
        $this->_application->getLocator()->addProviderClass(
            'ImageTransform',
            array(
                'driver' => $this->getConfig('driver'),
                'libPathIM' => $this->getConfig('im_path'),
                'libPathNetPBM' => $this->getConfig('netpbm_path'),
                'quality' => 75,
                'scaleMethod' => 'smooth',
                'canvasColor' => array(255, 255, 255),
                'pencilColor' => array(0, 0, 0),
                'textColor' => array(0, 0, 0)
            ),
            'Plugg_ImageTransform_ImageTransform',
            $this->_path . '/ImageTransform.php'
        );
    }

    public function onFormBuildSystemAdminSettings($form)
    {
        $form[$this->_name] = array(
            '#type' => 'fieldset',
            '#collapsible' => true,
            '#title' => $this->_('Image library settings'),
            '#tree' => true,
            'driver' => array(
                '#title' => $this->_('Image library to use to manipulate image files'),
                '#options' => array('IM' => 'Image Magick', 'NetPBM' => 'NetPBM', 'GD' => 'GD'),
                '#default_value' => $this->getConfig('driver'),
                '#required' => true,
                '#type' => 'radios',
            ),
            'im_path' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Path to ImageMagick binaries'),
                '#description' => $this->_('Set this option only when leaving it blank does not work with the library.'),
                '#collapsible' => true,
                '#collapsed' => true,
                '#default_value' => $this->getConfig('im_path'),
            ),
            'netpbm_path' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Path to NetPBM binaries'),
                '#description' => $this->_('Set this option only when leaving it blank does not work with the library.'),
                '#collapsible' => true,
                '#collapsed' => true,
                '#default_value' => $this->getConfig('netpbm_path'),
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
            'driver' => 'GD',
            'im_path' => '',
            'netpbm_path' => '',
        );
    }
}