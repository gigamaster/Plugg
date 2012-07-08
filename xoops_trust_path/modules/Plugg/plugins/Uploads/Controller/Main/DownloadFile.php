<?php
class Plugg_Uploads_Controller_Main_DownloadFile extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $file_path = $this->getPlugin()->getConfig('uploadDir') . '/' . $this->file->file_name;
        if (!file_exists($file_path) || (!$fp = fopen($file_path, 'rb'))) exit;
        
        header('Content-Disposition: attachment; filename="' . str_replace(array("\r", "\n", '"'), '', $this->file->name) . '"');
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $this->file->type);
        header('Content-Length: ' . ((!$file_size = @filesize($file_path)) ? $file_size : $this->file->size));
        if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        } else {
            header('Pragma: no-cache');
        }

        while (@ob_end_flush());
        while (!feof($fp)) echo fgets($fp, 2048);
        fclose($fp);
        
        exit;
    }
}