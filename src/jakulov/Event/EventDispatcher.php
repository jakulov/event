<?php
namespace jakulov\Event;

use jakulov\Container\DIContainer;
use Psr\Event\EventDispatcherInterface;
use Psr\Event\EventInterface;
use Psr\Event\EventSubscriberInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EventDispatcher
 * @package jakulov\Event
 */
class EventDispatcher implements EventDispatcherInterface, LoggerAwareInterface
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var array */
    protected $events = [];
    /** @var array */
    protected $sortedEvents = [];

    /**
     * @return DIContainer
     */
    protected function getDIContainer()
    {
        return DIContainer::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config = [])
    {
        $this->events = [];
        foreach($config as $eventName => $listeners) {
            foreach($listeners as $listener) {
                $this->events[$this->getListenerKeyHash($listener)] = $listener;
            }
        }
    }

    /**
     * @param array $listeners
     * @return array
     */
    protected function sortListeners(array $listeners)
    {
        $sorted = [];
        foreach($listeners as $listener) {
            $priority = $this->getListenerPriority($listener);
            if(!isset($sorted[$priority])) {
                $sorted[$priority] = [];
            }
            $sorted[$priority][$this->getListenerKeyHash($listener)] = $listener;
        }

        return $sorted;
    }

    /**
     * @param array $listener
     * @return int
     */
    protected function getListenerPriority(array $listener)
    {
        return isset($listener[2]) ? (int)isset($listener[2]) : 0;
    }

    /**
     * @param string $eventName
     * @param EventInterface|null $event
     * @return EventInterface
     */
    public function dispatch($eventName, EventInterface $event = null)
    {
        foreach($this->getSortedEventListeners($eventName) as $priority => $listeners) {
            foreach($listeners as $listener) {
                $this->logger->debug(
                    sprintf('Dispatching %s event to %s', $eventName, $this->dumpListener($listener))
                );
                $event = $this->callListener($listener, $event);
                if($event && $event->isPropagationStopped()) {
                    $this->logger->debug(sprintf('Propagation of event %s stopped', $eventName));
                    break 2;
                }
            }
        }

        return $event;
    }

    /**
     * @param array|string|callable $listener
     * @return string
     */
    protected function dumpListener($listener)
    {
        return str_replace(
            PHP_EOL, ' ',
            print_r( is_array($listener) ? array_slice($listener, 0, 2) : $listener, 1)
        );
    }

    /**
     * @param $listener
     * @param EventInterface|null $event
     * @return EventInterface
     * @throws \jakulov\Container\ContainerException
     */
    protected function callListener($listener, EventInterface $event = null) :
    {
        if(is_callable($listener)) {
            return call_user_func_array($listener, [$event, $this]);
        }
        list($class, $method) = $listener;
        if(strpos($class, '@') === 0) {
            $class = $this->getDIContainer()->get($class);
        }

        return call_user_func_array([$class, $method], [$event, $this]);
    }

    /**
     * @param $eventName
     * @return array
     */
    protected function getSortedEventListeners($eventName)
    {
        if(!isset($this->sortedEvents[$eventName])) {
            $this->sortedEvents[$eventName] = $this->sortListeners(
                isset($this->events[$eventName]) ? isset($this->events[$eventName]) : []
            );
            ksort($this->sortedEvents[$eventName]);

            // debug logs
            $this->logger->debug(sprintf('Registering listeners of event "%s"...', $eventName));
            foreach($this->sortedEvents[$eventName] as $priority => $listeners) {
                foreach($listeners as $listener) {
                    $this->logger->debug(sprintf(
                        '   Listener %s registered with priority %s', $this->dumpListener($listener), $priority
                    ));
                }
            }
        }

        return $this->sortedEvents[$eventName];
    }

    /**
     * @param $listener
     * @return string
     */
    protected function getListenerKeyHash($listener)
    {
        return base64_encode($this->dumpListener($listener));
    }

    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $key = $this->getListenerKeyHash($listener);
        $this->events[$eventName][$key] = $listener;

        if(!$this->sortedEvents[$eventName]) {
            $this->sortedEvents[$eventName] = [];
            $this->sortedEvents[$eventName][$priority] = [];
        }
        $this->sortedEvents[$eventName][$priority][$key] = $listener;

        $this->logger->debug(
            sprintf('Registering listener %s of event "%s"', $this->dumpListener($listener) , $eventName)
        );
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach($subscriber->getSubscribedEvents() as $eventName => $listeners)  {
            if(is_array($listeners) && is_array($listeners[0])) {
                foreach($listeners as $listener) {
                    $this->addListener($eventName, $listener, $this->getListenerPriority($listener));
                }
            }
            else {
                $this->addListener($eventName, $listeners, $this->getListenerPriority($listeners));
            }
        }
    }

    /**
     * @param array|string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener)
    {
        $key = $this->getListenerKeyHash($listener);

        if(isset($this->events[$eventName][$key])) {
            unset($this->events[$eventName][$key]);
        }
        if(isset($this->sortedEvents[$eventName])) foreach($this->sortedEvents[$eventName] as $p => $listeners) {
            if(isset($listeners[$key])) {
                unset($this->sortedEvents[$eventName][$p][$key]);
            }
        }

        $this->logger->debug(
            sprintf('Remove listener %s of event "%s"', $this->dumpListener($listener) , $eventName)
        );
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach($subscriber->getSubscribedEvents() as $eventName => $listeners)  {
            if(is_array($listeners) && is_array($listeners[0])) {
                foreach($listeners as $listener) {
                    $this->removeListener($eventName, $listener);
                }
            }
            else {
                $this->removeListener($eventName, $listeners);
            }
        }
    }

    /**
     * @param null $eventName
     * @return array
     */
    public function getListeners($eventName = null)
    {
        if($eventName === null) {
            return $this->events;
        }

        return isset($this->events[$eventName]) ? $this->events[$eventName] : [];
    }

    /**
     * @param null $eventName
     * @return bool
     */
    public function hasListeners($eventName = null)
    {
        if($eventName === null) {
            return count($this->events) > 0;
        }

        return isset($this->events[$eventName]) ? count($this->events[$eventName]) > 0 : false;
    }

}