<?php

return [
    'service' => [
        'event_dispatcher' => [
            'class' => \jakulov\Event\EventDispatcher::class,
            'aware' => [
                'setConfig' => ':event',
            ],
        ],
    ],
];