<?php
require_once 'Sabai/Page/Collection.php';

class Sabai_User_IdentityPageCollection extends Sabai_Page_Collection
{
    protected $_identityFetcher;
    protected $_sort;
    protected $_order;

    public function __construct(Sabai_User_IdentityFetcher $identityFetcher, $perpage, $sort, $order, $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_identityFetcher = $identityFetcher;
        $this->_sort = $sort;
        $this->_order = $order;
    }

    protected function _getElementCount()
    {
        return $this->_identityFetcher->countIdentities();
    }
    
    protected function _getElements($limit, $offset)
    {
        return $this->_identityFetcher->fetchIdentities($limit, $offset, $this->_sort, $this->_order);
    }
}