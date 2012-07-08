<?php
class Plugg_Forum_Model_CommentsWithAttachments extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('attachment_comment_id', 'Attachment', $collection);
    }
}