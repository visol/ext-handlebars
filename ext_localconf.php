<?php
if (!defined('TYPO3')) {
    die ('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['handlebars']['defaultDataProviders'] = [
    \JFB\Handlebars\DataProvider\TyposcriptDataProvider::class,
];
