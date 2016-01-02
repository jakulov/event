<?php

require __DIR__ .'/Event/TestEvent.php';
require __DIR__ .'/Event/AnotherTestEvent.php';
require __DIR__ .'/Listener/TestListener.php';
require __DIR__ .'/Listener/TestPrioritizedListener.php';
require __DIR__ .'/Listener/TestSubscribe.php';


/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 02.01.16
 * Time: 19:18
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{

    public function testSetConfig()
    {
        $listener1 = new \Listener\TestListener();
        $listener2 = new \Listener\TestPrioritizedListener();
        $config = [
            'event.test' => [
                [$listener1, 'onTest'],
                [$listener2, 'onTest'],
            ],
        ];

        $dispatcher = $this->getEventDispatcher($config);

        $this->assertEquals(true, $dispatcher->hasListeners('event.test'));
    }



    /**
     * @param array $config
     * @return \jakulov\Event\EventDispatcher
     */
    protected function getEventDispatcher($config = [])
    {
        $dispatcher = new \jakulov\Event\EventDispatcher();
        $dispatcher->setConfig($config);
        $dispatcher->setLogger($this->getLogger());

        return $dispatcher;
    }

    protected function getLogger()
    {
        return new \Psr\Log\NullLogger();
    }

    public function testHasListeners()
    {
        $this->testSetConfig();
    }

    public function testAddListener()
    {
        $listener = [new \Listener\TestListener(), 'onTest'];

        $event = new \Event\AnotherTestEvent();
        $ed = $this->getEventDispatcher();
        $ed->addListener($event->getName(), $listener);

        $this->assertEquals(true, $ed->hasListeners($event->getName()));
    }

    public function testRemoveListener()
    {
        $listener = function($event) {
            return $event;
        };

        $event = new \Event\AnotherTestEvent();
        $ed = $this->getEventDispatcher();
        $ed->addListener($event->getName(), $listener);

        $ed->removeListener($event->getName(), $listener);

        $this->assertEquals(false, $ed->hasListeners($event->getName()));
    }

    public function testDispatch()
    {
        $event1 = new \Event\TestEvent();

        $listener1 = new \Listener\TestListener();
        $listener2 = new \Listener\TestPrioritizedListener();
        $config = [
            'event.test' => [
                [$listener1, 'onTest'],
                [$listener2, 'onTest'],
            ],
        ];

        $dispatcher = $this->getEventDispatcher($config);

        $dispatcher->dispatch($event1->getName(), $event1);

        $this->assertEquals('bar', $listener1->foo, "listener 1 called");
        $this->assertEquals('bar', $listener2->foo, 'listener 2 called');
    }

    public function testDispatchWithStop()
    {
        $event1 = new \Event\TestEvent();

        $listener1 = new \Listener\TestListener();
        $listener2 = new \Listener\TestPrioritizedListener();
        $config = [
            'event.test' => [
                [$listener1, 'onTest'],
                [$listener2, 'onTest', 1],
            ],
        ];

        $dispatcher = $this->getEventDispatcher($config);

        $event1->foo = 'stop';
        $dispatcher->dispatch($event1->getName(), $event1);

        $this->assertEquals(null, $listener1->foo, "listener 1 called");
        $this->assertEquals('stop', $listener2->foo, 'listener 2 called');
    }

    public function testAddSubscriber()
    {
        $dic = \jakulov\Container\DIContainer::getInstance([
            'service' => [
                'listener.test' => [
                    'class' => \Listener\TestListener::class,
                ],
                'listener.test_prioritized' => [
                    'class' => \Listener\TestPrioritizedListener::class,
                ],
            ],
        ]);

        $config = [];
        $ed = $this->getEventDispatcher($config);

        $ed->addSubscriber(new \Listener\TestSubscriber());

        $this->assertEquals(true, $ed->hasListeners('event.test'));
        $this->assertEquals(true, $ed->hasListeners('event.another_test'));

        $event1 = new \Event\TestEvent();
        $ed->dispatch($event1->getName(), $event1);

        /** @var Listener\TestListener $listener1 */
        $listener1 = $dic->get('listener.test');

        $this->assertEquals('bar', $listener1->foo);
    }


}
