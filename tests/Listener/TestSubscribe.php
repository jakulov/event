<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 02.01.16
 * Time: 20:46
 */

namespace Listener;

use Psr\Event\EventSubscriberInterface;

/**
 * Class TestSubscriber
 * @package Listener
 */
class TestSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'event.test' => ['@listener.test', 'onTest'],
            'event.another_test' => [
                ['@listener.test', 'onTest'],
                ['@listener.test_prioritized', 'onTest', 1],
            ],
        ];
    }


}