<?php
class Plugg_Forum_Model_TopicsWithAttachments extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('attachment_topic_id', 'Attachment', $collection);
    }
}