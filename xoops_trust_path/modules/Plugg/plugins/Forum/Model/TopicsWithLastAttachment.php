<?php
class Plugg_Forum_Model_TopicsWithLastAttachment extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('attachment_last', 'Attachment', 'attachment_id', $collection);
    }
}