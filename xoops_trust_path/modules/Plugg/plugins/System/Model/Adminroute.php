<?php
class Plugg_System_Model_Adminroute extends Plugg_System_Model_Base_Adminroute
{
    public function toArray()
    {
        return array(
            'path' => $this->path,
            'controller' => $this->controller,
            'forward' => $this->forward,
            'plugin' => $this->plugin,
            'type' => $this->type,
            'title' => $this->title,
            'title_callback' => $this->title_callback,
            'access_callback' => $this->access_callback,
            'weight' => $this->weight,
            'format' => @unserialize($this->format),
        );
    }
}

class Plugg_System_Model_AdminrouteRepository extends Plugg_System_Model_Base_AdminrouteRepository
{
}