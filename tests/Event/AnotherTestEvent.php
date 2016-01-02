<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 02.01.16
 * Time: 19:57
 */

namespace Event;

use jakulov\Event\AbstractEvent;

/**
 * Class AnotherTestEvent
 * @package Event
 */
class AnotherTestEvent extends AbstractEvent
{
    protected $name = 'event.another_test';

    public $foo = 'another_bar';
}