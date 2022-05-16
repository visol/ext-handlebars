<?php

$EM_CONF['handlebars'] = [
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
            'typo3' => '7.6.*',
            'extbase' => '',
        ],
        'conflicts' => [
        ],
    ],
    '_md5_values_when_last_written' => 'a:0:{}',
    'suggests' => [
    ],
];
