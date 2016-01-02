<?php
namespace Event;

use jakulov\Event\AbstractEvent;

/**
 * Class TestEvent
 * @package Event
 */
class TestEvent extends AbstractEvent
{
    protected $name = 'event.test';

    public $foo = 'bar';
}