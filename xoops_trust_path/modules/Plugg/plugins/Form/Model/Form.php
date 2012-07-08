<?php
class Plugg_Form_Model_Form extends Plugg_Form_Model_Base_Form
{
    public function setAnonymousAccess($accessType, $flag = true)
    {
        foreach ((array)$accessType as $access_type) {
            $var = 'anon_' . $access_type;
            $this->$var = (bool)$flag;
        }
    }

    public function getEmailSettings()
    {
        return unserialize($this->email_settings);
    }
    
    public function setEmailSettings(array $settings)
    {
        $this->email_settings = serialize($settings);
    }
}

class Plugg_Form_Model_FormRepository extends Plugg_Form_Model_Base_FormRepository
{
}