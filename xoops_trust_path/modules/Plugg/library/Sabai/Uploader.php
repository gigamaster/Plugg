<?php
class Sabai_Uploader
{
    protected $_uploadDir,
        $_allowedExtensions, $_imageExtensions = array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
        $_maxSize, $_maxImageWidth, $_maxImageHeight,
        $_overwrite = false, $_permission = 0644,
        $_filenamePrefix = '', $_filenameMaxLength = 0,
        $_imageOnly = false;

    public static $mimeTypes = array(
        'hqx' => 'application/mac-binhex40',
        'csv' => array(
            'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream',
            'application/vnd.ms-excel', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'word' => array('application/msword', 'application/octet-stream'),
        'bin' => 'application/octet-stream',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'exe' => 'application/octet-stream',
        'class' => 'application/octet-stream',
        'so' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'pdf' => array('application/pdf', 'application/x-download'),
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'xla' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
        'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
        'xlt' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
        'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
        'csh' => 'application/x-csh',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'spl' => 'application/x-futuresplash',
        'gtar' => 'application/x-gtar',
        'php' => array('application/x-httpd-php', 'text/php'),
        'phps' => array('application/x-httpd-php', 'text/php', 'application/x-httpd-php-source'),
        'php3' => array('application/x-httpd-php', 'text/php'),
        'phtml' => array('application/x-httpd-php', 'text/php'),
        'js' => 'application/x-javascript',
        'sh' => 'application/x-sh',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'xhtml' => 'application/xhtml+xml',
        'xht' => 'application/xhtml+xml',
        'xhtml' => 'application/xml',
        'ent' => 'application/xml-external-parsed-entity',
        'dtd' => 'application/xml-dtd',
        'mod' => 'application/xml-dtd',
        'gz' => 'application/x-gzip',
        'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mp1' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mp3' => array('audio/mpeg', 'audio/mpg'),
        'aif' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'm3u' => 'audio/x-mpegurl',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'wav' => 'audio/x-wav',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tif',
        'wbmp' => 'image/vnd.wap.wbmp',
        'pnm' => 'image/x-portable-anymap',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'ppm' => 'image/x-portable-pixmap',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'ics' => 'text/calendar',
        'ifb' => 'text/calendar',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'asc' => 'text/plain',
        'txt' => 'text/plain',
        'rtf' => 'text/rtf',
        'sgml' => 'text/x-sgml',
        'sgm' => 'text/x-sgml',
        'tsv' => 'text/tab-seperated-values',
        'wml' => 'text/vnd.wap.wml',
        'wmls' => 'text/vnd.wap.wmlscript',
        'xsl' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
    );

    public static $imageTypes = array(
        'gif' => IMAGETYPE_GIF,
        'jpeg' => IMAGETYPE_JPEG,
        'jpg' => IMAGETYPE_JPEG,
        'jpe' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
        'bmp' => IMAGETYPE_BMP,
        'tif' => array(IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM),
        'tiff' => array(IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM),
        'swf' => IMAGETYPE_SWF
    );


    public function __construct($uploadDir, array $allowedExtensions = array())
    {
        $this->_uploadDir = $uploadDir;
        $this->_allowedExtensions = $allowedExtensions;
    }

    public function __set($name, $value)
    {
       $property = '_' . $name;
       if (property_exists($this, $property)) {
           $this->$property = $value;
       }
    }

    public function __get($name)
    {
       $property = '_' . $name;
       if (property_exists($this, $property)) {
           return $this->$property;
       }
    }

    public function upload($name)
    {
        if (!is_array($_FILES[$name]['name'])) {
            return $this->uploadFile($_FILES[$name]);
        }

        $ret = array();
        foreach (array_keys($_FILES[$name]['name']) as $i) {
            $_file = $_FILES[$name];
            $file = array(
                'name' => $_file['name'][$i],
                'tmp_name' => $_file['tmp_name'][$i],
                'type' => $_file['type'][$i],
                'size' => $_file['size'][$i],
            );
            $ret[$i] = $this->uploadFile($file);
        }

        return $ret;
    }

    public function uploadFile($file)
    {
        // Initialize the file array
        $file = array_merge($file, array(
           'upload_error' => null,
           'file_name' => null,
           'file_path' => null,
           'file_ext' => null,
           'no_file' => false,
           'is_image' => false,
           'image_width' => null,
           'image_height' => null,
        ));

        if ($file['error'] != UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $file['upload_error'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $file['upload_error'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $file['upload_error'] = 'The uploaded file was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $file['no_file'] = true;
                    $file['upload_error'] = 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $file['upload_error'] = 'Missing a temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $file['upload_error'] = 'Failed to write file to disk.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $file['upload_error'] = 'File upload stopped by extension.';
                    break;
            }

            return $file;
        }

        if (empty($file['tmp_name']) || $file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
            $file['upload_error'] = 'No valid file was found under the temporary folder.';

            return $file;
        }

        if (!$this->_checkExtensionAndMimeType($file)) {
            return $file;
        }

        if (!$this->_checkMaxSize($file['tmp_name'])) {
            $file['upload_error'] = sprintf('The uploaded file exceeds the max file size: %skb', round($this->_maxSize / 1024, 1));

            return $file;
        }

        // Some additional checks if image file
        if ($this->isImage($file['name'])) {
            $file['is_image'] = true;

            if (!$image_size = $this->_checkMaxImageDimension($file['tmp_name'])) {
                $file['upload_error'] = sprintf('The uploaded image file may not exceed the maximum image dimension: W%dxH%d', $this->_maxImageWidth, $this->_maxImageHeight);

                return $file;
            }
            $file['image_width'] = $image_size[0];
            $file['image_height'] = $image_size[1];
        } else {
            if ($this->_imageOnly) {
                $file['upload_error'] = sprintf('Only image files with one of the following extensions are allowed to upload: %s', implode(', ', $this->_imageExtensions));

                return $file;
            }
        }

        if (!is_dir($this->_uploadDir) || !is_writable($this->_uploadDir)) {
            $file['upload_error'] = sprintf('The upload directory is not writeable by the server: %s', $this->_uploadDir);

            return $file;
        }

        $file_name = self::getUniqueFileName($file['name'], $this->_uploadDir, $this->_filenamePrefix, $this->_filenameMaxLength, $this->_overwrite);
        $file_path = $this->_uploadDir . '/' . $file_name;
        if (!@move_uploaded_file($file['tmp_name'], $file_path)) {
            $file['upload_error'] = sprintf('Failed moving uploaded file to %s', $file_path);

            return $file;
        }

        @chmod($file_path, $this->_permission);
        $file['file_name'] = $file_name;
        $file['file_path'] = $file_path;

        return $file;
    }
    
    public function isImage($fileName)
    {
        return in_array(self::getFileExtension($fileName), $this->_imageExtensions);
    }

    public static function getFileExtension($fileName)
    {
        if (!$file_ext_pos = strrpos($fileName, '.')) return '';

        return strtolower(substr($fileName, $file_ext_pos + 1));
    }

    public static function getImageType($filePath)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($filePath);
        }

        if ($image_size = @getimagesize($filePath)) {
            return $image_size[2];
        }

        return false;
    }

    public static function getUniqueFileName($fileName, $uploadDir, $filenamePrefix = '', $filenameMaxLength = null, $overwrite = false)
    {
        $file_ext = self::getFileExtension($fileName);
        $filename_prefix = (string)$filenamePrefix;
        $filename_max_length = intval($filenameMaxLength);
        do {
            $filename_hash = md5(uniqid(mt_rand(), true));
            // truncate hash if the file name length will exceed the max file name length
            if (!empty($filename_max_length)
                && ($hash_maxlength = $filename_max_length - (strlen($filename_prefix) + strlen($file_ext) + 1))
                && strlen($filename_hash) > $hash_maxlength
            ) {
                $filename_hash = substr($filename_hash, 0, $hash_maxlength);
            }
            $file_name = $filename_prefix . $filename_hash . '.' . $file_ext;
        } while (!$overwrite && file_exists($uploadDir . '/' . $file_name));

        return $file_name;
    }

    protected function _checkExtensionAndMimeType(&$file)
    {
        $allowed_extensions = $this->_imageOnly ? $this->_imageExtensions : $this->_allowedExtensions;

        // There must be allowed extensions defined for additional security
        if (empty($allowed_extensions)) {
            $file['upload_error'] = 'Invalid file extension.';

            return false;
        }

        // Check file extension
        if ('' == $file['file_ext'] = self::getFileExtension($file['name'])) {
            $file['upload_error'] = 'Invalid file extension.';

            return false;
        }
        if (!in_array($file['file_ext'], $allowed_extensions)) {
            $file['upload_error'] = 'File extension not allowed.';

            return false;
        }

        // Return if no associated mime type for the file extension
        if (!$allowed_mime_types = @self::$mimeTypes[$file['file_ext']]) {
            $file['upload_error'] = 'No matching mime types were found.';

            return false;
        }

        // Check image type if the file is an image file
        if ($allowed_image_types = @self::$imageTypes[$file['file_ext']]) {
            if ($image_type = self::getImageType($file['tmp_name'])) {
                if (in_array($image_type, (array)$allowed_image_types)) {
                    return true;
                }
            }
        }

        // Check if the file mime type corresponds with the allowed mime types for the file extension
        foreach ((array)$allowed_mime_types as $allowed_mime_type) {
            if (!isset($file_mime)) {
                $file_mime = $file['type'];
                if (function_exists('finfo_open')) {
                    if ($finfo = @finfo_open(FILEINFO_MIME)) {
                        if ($file_finfo_mime = finfo_file($finfo, $file['tmp_name'])) {
                            $file_mime = $file_finfo_mime;
                        }
                        finfo_close($finfo);
                    }
                }
            }
            if (preg_match('#' . preg_quote($allowed_mime_type, '#') . '#', $file_mime)) {
                return true;
            }
        }

        // File extension does not match the expected file type.
        $file['upload_error'] = sprintf(
            'File extension does not match file type. Expected file type: %s',
            implode(', ', (array)$allowed_mime_types)
        );

        return false;
    }

    protected function _checkMaxSize($filePath)
    {
        if (empty($this->_maxSize)) return true;

        return @filesize($filePath) <= $this->_maxSize;
    }

    protected function _checkMaxImageDimension($filePath)
    {
        if (!$image_size = @getimagesize($filePath)) return false;

        if (!empty($this->_maxImageWidth) && $image_size[0] > $this->_maxImageWidth) return false;

        if (!empty($this->_maxImageHeight) && $image_size[1] > $this->_maxImageHeight) return false;

        return $image_size;
    }
}