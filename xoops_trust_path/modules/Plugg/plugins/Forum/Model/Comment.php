<?php
class Plugg_Forum_Model_Comment extends Plugg_Forum_Model_Base_Comment
{   
    public function getSummary($length = 500)
    {
        return mb_strimlength(strip_tags(strtr($this->body_html, array("\r" => '', "\n" => ''))), 0, $length);
    }
    
    public function getAttachmentFileIds()
    {
        $file_ids = array();
        foreach ($this->Attachments as $attachment) {
            $file_ids[] = $attachment->file_id;
        }

        return $file_ids;
    }
}

class Plugg_Forum_Model_CommentRepository extends Plugg_Forum_Model_Base_CommentRepository
{
}