<?php

return [
    'services' => [
        'others' => [
            'mautic.vara.helper' => [
                'class'     => \Mautic\VARABundle\Helper\VARAHelper::class,
                'arguments' => [
                    'monolog.logger.mautic',
                ],
            ],
        ],
    ]
];
