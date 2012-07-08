<?php
class Plugg_User_IdentityFetcher extends Sabai_User_IdentityFetcher
{
    protected $_plugin;

    public function __construct(Plugg_User_Plugin $plugin)
    {
        $this->_plugin = $plugin;
        $this->_idField = 'Id';
        $this->_usernameField = 'Username';
        $this->_nameField = 'Name';
        $this->_emailField = 'Email';
        $this->_urlField = 'Url';
        $this->_timestampField = 'Timestamp';
    }

    protected function _doFetchUserIdentities($userIds, $withData = false)
    {
        $identities = $this->_plugin->getManagerPlugin()->userFetchIdentitiesByIds($userIds);
        if ($withData) $this->loadIdentitiesWithData($identities);
        
        return $identities;
    }

    protected function _doFetchIdentities($limit, $offset, $sort, $order)
    {;
        $method = 'userFetchIdentitiesSortby' . $sort;
        return $this->_plugin->getManagerPlugin()->$method($limit, $offset, $order);
    }

    public function countIdentities()
    {
        return $this->_plugin->getManagerPlugin()->userCountIdentities();
    }

    protected function _doFetchUserIdentityByUsername($userName, $withData = false)
    {
        $identity = $this->_plugin->getManagerPlugin()->userFetchIdentityByUsername($userName);
        if ($withData) $this->loadIdentityWithData($identity);
        
        return $identity;
    }

    protected function _doFetchUserIdentityByEmail($email, $withData = false)
    {
        $identity = $this->_plugin->getManagerPlugin()->userFetchIdentityByEmail($email);
        if ($withData) $this->loadIdentityWithData($identity);
        
        return $identity;
    }

    protected function _getAnonymousUserIdentity()
    {
        return $this->_plugin->getManagerPlugin()->userGetAnonymousIdentity();
    }

    public function loadIdentityWithData(Sabai_User_Identity $identity)
    {
        $data = array();
        foreach ($this->_plugin->getModel()->Meta->fetchByUser($identity->id) as $meta) {
            $data[$meta->key] = $meta->serialized ? unserialize($meta->value) : $meta->value;
        }
        $identity->setData($data);
    }

    public function loadIdentitiesWithData(array $identities)
    {
        $data = array();
        foreach ($this->_plugin->getModel()->Meta->fetchByUser(array_keys($identities)) as $meta) {
            $data[$meta->user_id][$meta->key] = $meta->serialized ? unserialize($meta->value) : $meta->value;
        }
        foreach (array_keys($data) as $user_id) {
            $identities[$user_id]->setData($data[$user_id]);
        }
    }
}