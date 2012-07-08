<?php
class Plugg_Uploads_Model_FilesWithGroup extends Plugg_Groups_Model_WithGroup
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}