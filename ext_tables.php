<?php
if (!defined('TYPO3')) {
	die ('Access denied.');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('handlebars', 'Configuration/TypoScript', 'TS skeleton');
