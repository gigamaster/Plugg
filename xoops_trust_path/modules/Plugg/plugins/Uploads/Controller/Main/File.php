<?php
class Plugg_Uploads_Controller_Main_File extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$this->file->is_image) {
            $file_path = $this->getPlugin()->path . '/images/blank.gif';
            $content_type = 'image/gif';
        } else {
            $file_path = $this->getPlugin()->getConfig('uploadDir') . '/' . $this->file->file_name;
            if (!file_exists($file_path)) {
                $file_path = $this->getPlugin()->path . '/images/blank.gif';
                $content_type = 'image/gif';
            } else {
                $content_type = $this->file->type;
            }
        }

        $cache_limit = 432000; // 5 days
        if (!$file_mtime = @filemtime($file_path)) $file_mtime = time();

        header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $cache_limit));
        header('Cache-Control: public, max-age=' . $cache_limit);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $file_mtime));
        header('Content-Type: ' . $content_type);

        echo file_get_contents($file_path);
        
        exit;
    }
}