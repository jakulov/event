<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 02.01.16
 * Time: 19:52
 */

namespace Listener;

use Event\TestEvent;

/**
 * Class TestPrioritizedListener
 * @package Listener
 */
class TestPrioritizedListener
{
    public $foo;

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