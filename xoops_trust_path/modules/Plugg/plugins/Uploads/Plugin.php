<?php
class Plugg_Uploads_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_Form_Field
{
    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/uploads/:file_id' => array(
                'type' => Plugg::ROUTE_CALLBACK,
                'format' => array(':file_id' => '\d+'),
                'controller' => 'File',
                'access_callback' => true,
            ),
            '/uploads/:file_id/download' => array(
                'type' => Plugg::ROUTE_CALLBACK,
                'controller' => 'DownloadFile',
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/uploads/:file_id':
                return ($this->_application->file = $this->getRequestedEntity($request, 'File', 'file_id')) ? true : false;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Main */
    
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/uploads' => array(
                'controller' => 'Settings',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_Form_Field */

    public function formFieldGetFormElementTypes()
    {
        return array('uploads_file' => Plugg_Form_Plugin::FORM_FIELD_NORMAL);
    }
    
    public function formFieldGetTitle($type)
    {
        switch ($type) {
            case 'uploads_file':
                return $this->_('File upload and management field');
        }
    }
    
    public function formFieldGetSummary($type)
    {
        switch ($type) {
            case 'uploads_file':
                return $this->_('Displays a file upload field with file management capability.');
        }
    }

    public function formFieldGetFormElement($type, $name, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'uploads_file':
                $model = $this->getModel();
                
                // Define element settings
                $ele_data = array(
                    '#label' =>$data['#label'],
                    '#tree' => true,
                ) + $form->defaultElementSettings();
                
                // Add file upload field
                $data['#file_settings'] = $form->defaultElementSettings();
                $data['#file_settings']['#type'] = 'file';
                $data['#file_settings']['#label'] = array($this->_('Upload new file'));
                if ($this->getConfig('images', 'thumbnailEnable')) {
                    $data['#file_settings']['#thumbnail_dir'] = $this->getConfig('images', 'thumbnailDir');
                    $data['#file_settings']['#thumbnail_width'] = $this->getConfig('images', 'thumbnailWidth');
                    $data['#file_settings']['#thumbnail_height'] = $this->getConfig('images', 'thumbnailHeight');
                }
                $data['#file_settings']['#max_image_width'] = $this->getConfig('images', 'maxImageWidth');
                $data['#file_settings']['#max_image_height'] = $this->getConfig('images', 'maxImageHeight');
                if (isset($data['#file_name_prefix'])) $data['#file_settings']['#file_name_prefix'] = $data['#file_name_prefix'];

                $ele_data['#children'][0]['upload'] = $data['#file_settings'];
                
                // Add current file checkbox options
                if (!empty($data['#current_files'])) {
                    $current_file_options = array();
                    foreach ($model->File->fetchByIds($data['#current_files'])as $file) {
                        $current_file_options[$file->id] = array(
                            'name' => h($file->name),
                            'size' => $file->size >= 10000 ? sprintf('%dKB', $file->size / 1024) : sprintf('%dB', $file->size),
                            'type' => $file->type,
                            'link' => $file->is_image
                                ? sprintf('<a href="%s" class="colorbox" title="%s">%s</a>', $this->_application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/' . urlencode($file->name), 'script' => 'main')), h($file->name), $this->_('View'))
                                : sprintf('<a href="%s">%s</a>', $this->_application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/download/' . urlencode($file->name), 'script' => 'main')), $this->_('Download'))
                        );
                    }
                    if (!empty($current_file_options)) {
                        $ele_data['#children'][0]['current'] = array(
                            '#title' => $this->_('Current attachments:'),
                            '#type' => 'tableselect',
                            '#header' => array('name' => $this->_('Name'), 'size' => $this->_('Size'), 'type' => $this->_('type'), 'link' => ''),
                            '#options' => $current_file_options,
                            '#default_value' => array_keys($current_file_options),
                            '#multiple' => true,
                        ) + $form->defaultElementSettings();
                    }
                }
 
                // Add user file options if any
                $data['#user_file_options'] = array();
                if (empty($data['#user_id'])) $data['#user_id'] = $this->_application->getUser()->id;
                $criteria = $model->createCriteria('File')->userId_is($data['#user_id']);
                if (!empty($data['#current_files'])) $criteria->id_notIn($data['#current_files']);
                foreach ($model->File->fetchByCriteria($criteria, 0, 0, 'name', 'ASC') as $file) {
                    $data['#user_file_options'][$file->id] = array(
                        'name' => h($file->name),
                        'size' => $file->size >= 10000 ? sprintf('%dKB', $file->size / 1024) : sprintf('%dB', $file->size),
                        'type' => $file->type,
                        'link' => $file->is_image
                            ? sprintf('<a href="%s" class="colorbox" title="%s">%s</a>', $this->_application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/' . $file->name, 'script' => 'main')), h($file->name), $this->_('View'))
                            : sprintf('<a href="%s">%s</a>', $this->_application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/download/' . $file->name, 'script' => 'main')), $this->_('Download'))
                    );
                }
                if (!empty($data['#user_file_options'])) {
                    $ele_data['#children'][0]['user'] = array(
                        '#title' => $this->_('Add from your file list'),
                        '#type' => 'tableselect',
                        '#header' => array('name' => $this->_('Name'), 'size' => $this->_('Size'), 'type' => $this->_('type'), 'link' => ''),
                        '#options' => $data['#user_file_options'],
                        '#collapsible' => true,
                        '#collapsed' => true,
                        '#multiple' => true,
                    ) + $form->defaultElementSettings();
                }

                return $form->createElement('fieldset', $name, $ele_data);
        }
    }

    public function formFieldOnSubmitForm($type, $name, &$value, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'uploads_file':
                $result = $this->_application->getPlugin('Form')->formFieldOnSubmitForm('file', $name . '[upload]', $value['upload'], $data['#file_settings'], $form);
                $data['#uploaded_files'] = isset($data['#file_settings']['#uploaded_files']) ? $data['#file_settings']['#uploaded_files'] : array();
                if (false === $result || $form->hasError()) {
                    if ($form->hasError($name . '[upload]')) $form->setError($this->_('An error occurred.'), $name);
                    
                    return false;   
                }
                
                $file_ids = array();
                
                if (isset($value['upload'])) {
                    $file = $this->getModel()->create('File');
                    $file->name = $value['upload']['name'];
                    $file->file_name = $value['upload']['file_name'];
                    $file->size = $value['upload']['size'];
                    $file->type = $value['upload']['type'];
                    $file->is_image = $value['upload']['is_image'];
                    $file->image_width = $value['upload']['image_width'];
                    $file->image_height = $value['upload']['image_height'];
                    $file->thumbnail = $value['upload']['thumbnail'];
                    $file->thumbnail_width = $value['upload']['thumbnail_width'];
                    $file->thumbnail_height = $value['upload']['thumbnail_height'];
                    $file->user_id = $data['#user_id'];
                    $file->group_id = intval(@$data['#group_id']);
                    $file->protected = !empty($data['#protect_files']);
                    $file->markNew();
                
                    if (!$file->commit()) {
                        $form->setError($this->_('Failed saving file data to the database.', $name));
                    
                        return false;
                    }
                    
                    // Save the ID of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                    $data['#uploaded_file_ids'][] = $file->id;
                    
                    $file_ids[$file->id] = $file->id;
                }

                // Any current file selected?
                if (!empty($data['#current_files']) && !empty($value['current'])) {
                    foreach ($value['current'][0] as $file_id) {
                        if (!in_array($file_id, $data['#current_files'])) return false;

                        $file_ids[$file_id] = $file_id;
                    }
                }
                
                // Any user file selected?
                if (!empty($data['#user_file_options']) && !empty($value['user'])) {
                    foreach ($value['user'][0] as $file_id) {
                        if (!isset($data['#user_file_options'][$file_id])) return false;

                        $file_ids[$file_id] = $file_id;
                    }
                }
                
                $value = $file_ids;
        }
    }
    
    public function formFieldOnCleanupForm($type, $name, array $data, Plugg_Form_Form $form)
    {
        if ($form->isSubmitted()) return; 
        
        // Form submission failed, so remove the files that have been uploaded in the process
        if (!empty($data['#uploaded_files'])) {
            foreach ($data['#uploaded_files'] as $file_path) @unlink($file_path);
        }

        if (!empty($data['#uploaded_file_ids'])) {
            $model = $this->getModel();
            foreach ($model->File->fetchByIds($data['#uploaded_file_ids']) as $file) {
                $file->markRemoved();
            }
            $model->commit();
        }
    }

    public function formFieldGetSettings($type, array $currentValues)
    {
        switch ($type) {
            case 'uploads_file':
                return $this->_application->getPlugin('Form')->formFieldGetSettings('textarea', $currentValues);
        }
    }
    
    public function formFieldRenderHtml($type, $value, array $data, array $allValues = array())
    {
        switch ($type) {
            case 'uploads_file':
                return;
        }
    }

    /* End implementation of Plugg_Form_Field */

    public function onPluggInit()
    {
        // Register service
        $this->_application->getLocator()->addProviderFactoryMethod(
            'Uploader',
            array($this, 'createUploader'),
            array(
                'allowedExtensions' => null,
                'maxSize' => null,
                'maxImageWidth' => null,
                'maxImageHeight' => null,
                'overwrite' => false,
                'permission' => 0644,
                'filenamePrefix' => '',
                'filenameMaxLength' => 0,
                'imageExtensions' => null,
                'imageOnly' => null,
                'uploadDir' => null
            )
        );
    }

    public function createUploader($allowedExtensions, $maxSize, $maxImageWidth, $maxImageHeight, $overwrite, $permission,
        $filenamePrefix, $filenameMaxLength, $imageExtensions, $imageOnly, $uploadDir
    ) {
        $uploader = new Sabai_Uploader(
            $uploadDir ? $uploadDir : $this->getConfig('uploadDir'),
            (!empty($allowedExtensions) && is_array($allowedExtensions)) ? $allowedExtensions : $this->getConfig('allowedExtensions')
        );
        $uploader->maxSize = $maxSize ? intval($maxSize) : $this->getConfig('maxSizeKB') * 1024;
        $uploader->maxImageWidth = $maxImageWidth ? intval($maxImageWidth) : $this->getConfig('images', 'maxImageWidth') * 1024;
        $uploader->maxImageHeight = $maxImageHeight ? intval($maxImageHeight) : $this->getConfig('images', 'maxImageHeight') * 1024;
        $uploader->overwrite = (bool)$overwrite;
        $uploader->permission = $permission;
        $uploader->filenamePrefix = strval($filenamePrefix);
        $uploader->filenameMaxLength = intval($filenameMaxLength);
        $uploader->imageExtensions = (!empty($imageExtensions) && is_array($imageExtensions)) ? $imageExtensions : array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        $uploader->imageOnly = isset($imageOnly) ? (bool)$imageOnly : $this->getConfig('imageOnly');

        return $uploader;
    }
    
    public function getDefaultConfig()
    {
        if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
            // see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
            switch (strtoupper($matches['2'])) {
                case 'G':
                    $upload_max_filesize_kb = $matches['1'] * 1048576;
                    break;
                case 'M':
                    $upload_max_filesize_kb = $matches['1'] * 1024;
                    break;
                case 'K':
                    $upload_max_filesize_kb = $matches['1'];
                    break;
                default:
                    if (1 > $upload_max_filesize_kb = $matches['1'] / 1024) {
                        $upload_max_filesize_kb = 1;
                    }
            }
        }
        
        return array(
            'allowedExtensions' => array('gif', 'jpeg', 'jpg', 'pdf', 'png', 'swf', 'txt', 'zip'),
            'maxSizeKB' => $upload_max_filesize_kb,
            'imageOnly' => false,
            'images' => array(
                'thumbnailEnable' => true,
                'thumbnailWidth' => 100,
                'thumbnailHeight' => 100,
                'maxImageWidth' => 800,
                'maxImageHeight' => 600,
            ),
        );
    }
}