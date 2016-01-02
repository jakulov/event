# Event #
Simple & clear PHP Event Dispatcher, compatible with Symfony Events

Can be installed with composer

    composer require jakulov/event
    
Implements [Symfony Event Interfaces](https://packagist.org/packages/symfony/event-dispatcher)

## 1. Dispatcher ##
There's three common ways to register listeners: setConfig, addListener and use Subscriber class.
Config could look like this:

    $listener1 = new \Listener\TestListener();
    $listener2 = new \Listener\TestPrioritizedListener();
    $config = [
        'event.test' => [  // the name of event
            [$listener1, 'onTest', 1], // listener, method and priority
            [$listener2, 'onTest'],
        ],
        'event.another_test' => [
            ['@service.listener', 'onAnotherTest'], // you can use reference to DIContainer service
        ],
    ];
    $dispatcher = new \jakulov\Event\EventDispatcher();
    $dispatcher->setConfig($config);
    
    
Link to container library: [jakulov/container](https://packagist.org/packages/jakulov/container)

Using addListener is more usual way:

    $listener = [new \Listener\TestListener(), 'onTest'];
    $dispatcher = new \jakulov\Event\EventDispatcher();
    $dispatcher->addListener($event->getName(), $listener);
    
Also dispatcher requires a Logger instance, implementing [Psr Log](https://packagist.org/packages/psr/log)
If don't need in logging events, just do this:

    $dispatcher = new \jakulov\Event\EventDispatcher();
    $dispatcher->setLogger(new \Psr\Log\NullLogger());
    
## 2. Subscriber ##
Using subscriber class is usual way to setup multiple event listeners at once:

    $dispatcher = new \jakulov\Event\EventDispatcher();
    $dispatcher->addSubscriber(new \Listener\TestSubscriber());


## 3. Event ##
Class \jakulov\Event\AbstractEvent should be parent for all event objects used with dispatcher. You can dispatch events without event object, but objects is the right way to pass event data to listeners.
    
## Tests ##

Run:
./run_tests.sh

Tests are also examples for usage library