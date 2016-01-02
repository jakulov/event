<?php
namespace Listener;

use Event\TestEvent;

/**
 * Class TestListener
 * @package Listener
 */
class TestListener
{
    public   $foo;

    /**
     * @param TestEvent $event
     * @return TestEvent
     */
    public function onTest(TestEvent $event)
    {
        $this->foo = $event->foo;

        if($event->foo === 'stop') {
            $event->stopPropagation();
        }

        return $event;
    }
}