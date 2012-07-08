<?php
class Plugg_Uploads_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'Uploads' => array(
                '#type' => 'fieldset',
                '#tree' => true,
                'uploadDir' => array(
                    '#title' => $this->_('File upload directory'),
                    '#description' => $this->_('Enter the full path to a directory where uploaded files are stored. The directory should be outside the document root where files can not be accessed directly by the web browser.'),
                    '#type' => 'textfield',
                    '#default_value' => $this->getPlugin()->getConfig('uploadDir'),
                    '#required' => true,
                ),
                'allowedExtensions' => array(
                    '#title' => $this->_('Permitted file extensions'),
                    '#description' => $this->_('Only files with the specified file extensions will be allowed to upload. Separate extensions with spaces and do not include the leading dot.'),
                    '#type' => 'textmulti',
                    '#required' => true,
                    '#separator' => ' ',
                    '#rows' => 2,
                    '#default_value' => $this->getPlugin()->getConfig('allowedExtensions'),
                ),
                'imageOnly' => array(
                    '#title' => $this->_('Allow image files only'),
                    '#description' => $this->_('Check this option to allow only image files to be uploaded. This will override the permitted file extensions setting above and will only allow files with one of the following extensions: jpg,jpeg,gif,png,bmp '),
                    '#type' => 'checkbox',
                    '#default_value' => $this->getPlugin()->getConfig('imageOnly'),
                ),
                'maxSizeKB' => array(
                    '#title' => $this->_('Maximum file size'),
                    '#description' => $this->_('Enter the maximum file size per upload in kilo bytes. The value should not exceed the upload_max_filesize setting in php.ini.'),
                    '#numeric' => true,
                    '#size' => 6,
                    '#field_suffix' => 'KB',
                    '#default_value' => $this->getPlugin()->getConfig('maxSizeKB'),
                    '#required' => true,
                ),
                'images' => array(
                    '#title' => $this->_('Image file settings'),
                    'thumbnailEnable' => array(
                        '#title' => $this->_('Generate thumbnails'),
                        '#description' => $this->_('Check this option (recommended) to automatically generate thumbnails for uploaded image files.'),
                        '#type' => 'checkbox',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'thumbnailEnable'),
                    ),
                    'thumbnailDir' => array(
                        '#title' => $this->_('Thumbnail file directory'),
                        '#description' => $this->_('Enter the full path to a directory where thumbnail files are stored. The directory should be inside the document root so that image files can be accessed directly by the web browser.'),
                        '#type' => 'textfield',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'thumbnailDir'),
                        '#required' => true,
                    ),
                    'thumbnailUrl' => array(
                        '#title' => $this->_('Thumbnail file directory URL'),
                        '#description' => $this->_('Enter the URL to the thumbnail file directory, without a trailing slash.'),
                        '#type' => 'url',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'thumbnailUrl'),
                        '#required' => true,
                        '#allow_localhost' => true,
                    ),
                    'thumbnailWidth' => array(
                        '#title' => $this->_('Thumbnail image width'),
                        '#description' => $this->_('Enter the width of thumbnail image files in pixels.'),
                        '#numeric' => true,
                        '#size' => 5,
                        '#field_suffix' => 'px',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'thumbnailWidth'),
                        '#required' => true,
                    ),
                    'thumbnailHeight' => array(
                        '#title' => $this->_('Thumbnail image height'),
                        '#description' => $this->_('Enter the height of thumbnail image files in pixels.'),
                        '#numeric' => true,
                        '#size' => 5,
                        '#field_suffix' => 'px',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'thumbnailHeight'),
                        '#required' => true,
                    ),
                    'maxImageWidth' => array(
                        '#title' => $this->_('Maximum image width'),
                        '#description' => $this->_('Enter the maximum width of image files in pixels or 0 for no limit.'),
                        '#numeric' => true,
                        '#size' => 5,
                        '#field_suffix' => 'px',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'maxImageWidth'),
                        '#required' => true,
                    ),
                    'maxImageHeight' => array(
                        '#title' => $this->_('Maximum image height'),
                        '#description' => $this->_('Enter the maximum height of image files in pixels or 0 for no limit.'),
                        '#numeric' => true,
                        '#size' => 5,
                        '#field_suffix' => 'px',
                        '#default_value' => $this->getPlugin()->getConfig('images', 'maxImageHeight'),
                        '#required' => true,
                    ),
                ),
            ),
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        foreach (array('uploadDir') as $dir) {
            if (empty($form->values['Uploads'][$dir]) || !is_dir($form->values['Uploads'][$dir])) {
                $form->setError($this->_('Invalid directory. Please make sure the directory exists.'), sprintf('Uploads[images][%s]', $dir));
                continue;
            } elseif (!is_writeable($form->values['Uploads'][$dir])) {
                $form->setError($this->_('The directory must be writeable by the web server.'), sprintf('Uploads[images][%s]', $dir));
                continue;
            }
            $form->values['Uploads'][$dir] = rtrim($form->values['Uploads'][$dir], '/');
        }
        foreach (array('thumbnailDir') as $dir) {
            if (empty($form->values['Uploads']['images'][$dir]) || !is_dir($form->values['Uploads']['images'][$dir])) {
                $form->setError($this->_('Invalid directory. Please make sure the directory exists.'), sprintf('Uploads[images][%s]', $dir));
                continue;
            } elseif (!is_writeable($form->values['Uploads']['images'][$dir])) {
                $form->setError($this->_('The directory must be writeable by the web server.'), sprintf('Uploads[images][%s]', $dir));
                continue;
            }
            $form->values['Uploads']['images'][$dir] = rtrim($form->values['Uploads']['images'][$dir], '/');
        }
        if (!empty($form->values['Uploads']['images']['thumbnailUrl'])) {
            $form->values['Uploads']['images']['thumbnailUrl'] = rtrim($form->values['Uploads']['images']['thumbnailUrl'], '/');
        }
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Uploads'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Uploads'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}