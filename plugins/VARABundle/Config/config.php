<?php

/*
 * Plugin configuration
 * For configuration documentation, see https://developer.mautic.org/#plugin-config-file
 */
return [
    'name'        => 'VARA Integration',
    'description' => 'VARA Integration Plugin',
    'version'     => '1.0',
    'author'      => 'MPS',
    'services'    => [
      'events'    => [
          'mautic.vara.emailbundle.subscriber' => [
              'class' => 'MauticPlugin\VARABundle\EventListener\EmailSubscriber',
          ],
      ]
    ]
];
