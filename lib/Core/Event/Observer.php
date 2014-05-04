<?php

namespace Lib\Core\Event;

use Phalcon\Events\EventsAwareInterface;

class Observer implements EventsAwareInterface
{
    protected $_eventsManager;

    public function setEventsManager($eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    public function getEventsManager()
    {
        return $this->_eventsManager;
    }

    public function someTask()
    {
        $this->_eventsManager->fire("my-component:beforeSomeTask", $this);

        // do some task

        $this->_eventsManager->fire("my-component:afterSomeTask", $this);
    }


}