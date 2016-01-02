<?php
namespace jakulov\Event;

use Psr\Event\EventDispatcherInterface;
use Psr\Event\EventInterface;
use Psr\Event\EventSubscriberInterface;

/**
 * Class EventDispatcher
 * @package jakulov\Event
 */
class EventDispatcher implements EventDispatcherInterface
{
    public function dispatch($eventName, EventInterface $event = null)
    {
        // TODO: Implement dispatch() method.
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        // TODO: Implement addListener() method.
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement addSubscriber() method.
    }

    public function removeListener($eventName, $listener)
    {
        // TODO: Implement removeListener() method.
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement removeSubscriber() method.
    }

    public function getListeners($eventName = null)
    {
        // TODO: Implement getListeners() method.
    }

    public function hasListeners($eventName = null)
    {
        // TODO: Implement hasListeners() method.
    }

}