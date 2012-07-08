<?php
require_once 'Sabai/Event.php';

class Sabai_Event_Dispatcher
{
    protected $_events = array();
    protected $_listenerHandles = array();

    public function addListener($eventType, Sabai_Handle $listenerHandle, $listenerName)
    {
        foreach ((array)$eventType as $event_type) {
            $this->_events[strtolower($event_type)][] = $listenerName;
            $this->_listenerHandles[$listenerName] = $listenerHandle;
        }
    }

    public function dispatch($eventType, $eventArgs = array(), $listenerName = null, $force = false)
    {
        if (isset($listenerName)) {
            $this->dispatchListenerEvent($listenerName, new Sabai_Event($eventType, $eventArgs), $force);
        } else {
            $this->dispatchEvent(new Sabai_Event($eventType, $eventArgs), $force);
        }
    }

    public function dispatchEvent(Sabai_Event $event, $force = false)
    {
        $type = strtolower($event->getType());
        if ($listeners = @$this->_events[$type]) {
            foreach ($listeners as $listener_name) {
                if (!$this->_doDispatchEvent($listener_name, $event)) {
                    break;
                }
            }
        }
    }

    public function dispatchListenerEvent($listenerName, Sabai_Event $event, $force = false)
    {
        $type = strtolower($event->getType());
        if (!empty($this->_events[$type]) && in_array($listenerName, $this->_events[$type])) {
            $this->_doDispatchEvent($listenerName, $event);
        }
    }

    protected function _doDispatchEvent($listenerName, Sabai_Event $event)
    {
        $listener = $this->getListener($listenerName);
        Sabai_Log::info(sprintf('Event listener "%s" executed', $listenerName));
        if (false === $listener->dispatchEvent($event)) {
            return false;
        }
        return true;
    }

    public function getListener($listenerName)
    {
        return $this->_listenerHandles[$listenerName]->instantiate();
    }

    public function getListenerHandle($listenerName)
    {
        return $this->_listenerHandles[$listenerName];
    }

    public function listenerExists($listenerName)
    {
        return isset($this->_listenerHandles[$listenerName]);
    }

    public function getListenerNames($eventType)
    {
        $event_type = strtolower($eventType);
        return isset($this->_events[$event_type]) ? $this->_events[$event_type] : array();
    }

    public function clear()
    {
        $this->_events = $this->_listenerHandles = array();
    }
}