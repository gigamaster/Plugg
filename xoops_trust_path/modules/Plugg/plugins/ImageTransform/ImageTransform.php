<?php
require_once 'Image/Transform.php';

class Plugg_ImageTransform_ImageTransform
{
    private $_imageTransform;

    public function __construct($driver, $libPathIM, $libPathNetPBM, $quality, $scaleMethod, $canvasColor, $pencilColor, $textColor)
    {
        switch ($driver) {
            case 'IM':
                if ($libPathIM && !defined('IMAGE_TRANSFORM_IM_PATH')) {
                    define('IMAGE_TRANSFORM_IM_PATH', $libPathIM);
                }
                break;
            case 'NetPBM':
                if ($libPathNetPBM && !defined('IMAGE_TRANSFORM_NETPBM_PATH')) {
                    define('IMAGE_TRANSFORM_NETPBM_PATH', $libPathNetPBM);
                }
                break;
            default:
                $driver = 'GD';
        }

        $image_transform = Image_Transform::factory($driver);

        // Convert PEAR error to exception
        if (PEAR::isError($image_transform)) {
            throw new Plugg_Exception(
                sprintf(
                    'Image_Transform driver %s could not be initialized. Error: %s',
                    $driver,
                    $image_transform->getMessage()
                )
            );
        }

        // Set library options
        $image_transform->setOptions(array(
            'quality' => $quality,
            'scaleMethod' => $scaleMethod,
            'canvasColor' => $canvasColor,
            'pencilColor' => $pencilColor,
            'textColor' => $textColor
        ));
        $this->_imageTransform = $image_transform;
    }

    public function __call($method, $args)
    {
        if (!method_exists($this->_imageTransform, $method)) {
            throw new Plugg_Exception(sprintf('Call to undefined method %s::%s()', get_class($this->_imageTransform), $method));
        }

        $ret = call_user_func_array(array($this->_imageTransform, $method), $args);
        if (PEAR::isError($ret)) {
            // Convert PEAR error to exception
            throw new Plugg_Exception($ret->getMessage());
        }

        return $ret;
    }
}