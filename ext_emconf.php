<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Handlebars',
    'description' => 'Handlebars compiler based on open-source project "lightncandy" (https://github.com/zordius/lightncandy)',
    'category' => 'misc',
    'version' => '1.0.0',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Fabien Udriot, Alessandro Bellafronte',
    'author_email' => 'fabien.udriot@visol.ch, alessandro@4eyes.ch',
    'author_company' => 'Visol digitale Dienstleistungen GmbH, 4eyes GmbH',
    'autoload' =>
        [
            'psr-4' => ['JFB\\Handlebars\\' => 'Classes']
        ],
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-0.0.0',
            'typo3' => '8.7.13-11.5.99',
            'extbase' => '',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    '_md5_values_when_last_written' => 'a:0:{}',
];
