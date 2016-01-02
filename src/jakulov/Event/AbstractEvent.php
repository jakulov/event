<?php
namespace jakulov\Event;

use Psr\Event\EventDispatcherInterface;
use Psr\Event\EventInterface;

/**
 * Class AbstractEvent
 * @package jakulov\Event
 */
abstract class AbstractEvent implements EventInterface
{
    protected $name = 'abstract';
    /** @var bool */
    protected $propagationStopped = false;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * Stops propagation
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}