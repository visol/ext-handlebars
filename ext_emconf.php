<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Handlebars',
    'description' => 'Handlebars compiler based on open-source project "lightncandy" (https://github.com/zordius/lightncandy)',
    'category' => 'misc',
    'shy' => 0,
    'version' => '1.0.0',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'author' => 'Fabien Udriot, Alessandro Bellafronte',
    'author_email' => 'fabien.udriot@visol.ch, alessandro@4eyes.ch',
    'author_company' => 'Visol digitale Dienstleistungen GmbH, 4eyes GmbH',
    'autoload' =>
        [
            'psr-4' => ['JFB\\Handlebars\\' => 'Classes']
        ],
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.*',
            'extbase' => '',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    '_md5_values_when_last_written' => 'a:0:{}',
    'suggests' => [
    ],
];
