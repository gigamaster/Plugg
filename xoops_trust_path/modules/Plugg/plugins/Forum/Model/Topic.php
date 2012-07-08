<?php
class Plugg_Forum_Model_Topic extends Plugg_Forum_Model_Base_Topic
{
    public function getTitle($length = 0)
    {
        $title = !empty($length) ? mb_strimlength($this->title, 0, $length) : $this->title;
        
        // Closed?
        if ($this->closed) {
            $title = sprintf($this->_model->_('[closed] %s'), $title);
        }
        // Sticky?
        if ($this->sticky) {
            $title = sprintf($this->_model->_('[sticky] %s'), $title);
        }
        
        return $title;
    }
    
    public function getSummary($length = 500)
    {
        return mb_strimlength(strip_tags(strtr($this->body_html, array("\r" => '', "\n" => ''))), 0, $length);
    }
    
    public function getClasses()
    {
        $classes = array();
        
        // Closed?
        if ($this->closed) {
            $classes[] = 'forum-topic-closed';
        }
        // Sticky?
        if ($this->sticky) {
            $classes[] = 'forum-topic-sticky';
        }
        // Attachments?
        if ($this->attachment_count) {
            $classes[] = 'forum-topic-attached';
        }
        
        return $classes;
    }
    
    public function getAttachments()
    {
        return $this->_model->Attachment->criteria()->commentId_is(0)->topicId_is($this->id)->fetch();
    }
    
    public function getAttachmentFileIds()
    {
        $file_ids = array();
        foreach ($this->getAttachments() as $attachment) {
            $file_ids[] = $attachment->file_id;
        }
        
        return $file_ids;
    }
}

class Plugg_Forum_Model_TopicRepository extends Plugg_Forum_Model_Base_TopicRepository
{
}