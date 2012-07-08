<?php
class Plugg_Aggregator_Model_Item extends Plugg_Aggregator_Model_Base_Item
{
    public function getAuthorHTMLLink()
    {
        if ($this->author_link) {
            return sprintf('<a href="%s">%s</a>', h($this->author_link), h($this->author));
        } else {
            return h($this->author);
        }
    }

    public function getCategories()
    {
        return unserialize($this->categories);
    }

    public function getSummary($length = 255)
    {
        return mb_strimlength(strip_tags(strtr($this->body, array("\r" => '', "\n" => ''))), 0, $length);
    }
}

class Plugg_Aggregator_Model_ItemRepository extends Plugg_Aggregator_Model_Base_ItemRepository
{
}