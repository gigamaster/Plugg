<?php
class Plugg_Helper_Uploads_FileList extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, array $fileIds, $merge = false)
    {
        $filelist = array('images' => array(), 'files' => array());
        
        foreach ($application->getPluginModel('Uploads')->File->fetchByIds($fileIds) as $file) {
            $filelist[$file->is_image ? 'images' : 'files'][] = $this->_toArray($application, $file);
        }
        
        return $merge ? array_merge($filelist['images'], $filelist['files']) : $filelist;
    }
    
    private function _toArray($application, $file)
    {
        if (!$file->is_image) {
            $url = $application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/download/' . urlencode($file->name), 'script' => 'main'));
            $thumbnail_url = null;
        } else {
            $url = $application->createUrl(array('base' => '/uploads', 'path' => $file->id . '/' . urlencode($file->name), 'script' => 'main'));
            $thumbnail_url = $application->Uploads_ThumbnailUrl($file->thumbnail);
        }
        
        return array(
            'name' => $file->name,
            'file_name' => $file->file_name,
            'type' => $file->type,
            'size' => $file->size,
            'image_width' => $file->image_width,
            'image_height' => $file->image_height,
            'thumbnail' => $file->thumbnail,
            'thumbnail_width' => $file->thumbnail_width,
            'thumbnail_height' => $file->thumbnail_height,
            'url' => $url,
            'thumbnail_url' => $thumbnail_url,
        );
    }
}