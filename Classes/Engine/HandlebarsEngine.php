<?php
namespace JFB\Handlebars\Engine;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Fabien Udriot <fabien.udriot@visol.ch>, Visol digitale Dienstleistungen GmbH
 *  (c) 2017 Alessandro Bellafronte <alessandro@4eyes.ch>, 4eyes GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use JFB\Handlebars\DataProvider\DataProviderInterface;
use LightnCandy\LightnCandy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class HandlebarsEngine
 */
class HandlebarsEngine
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $extensionKey;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var array
     */
    protected $dataProviders;

    /**
     * @var array
     */
    protected $additionalData;

    /**
     * @var string
     */
    protected $tempPath;

    /**
     * HandlebarsEngine constructor.
     *
     * @param $settings array
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->extensionKey = $settings['extensionKey'];
        $this->controllerName = $settings['controllerName'];
        $this->actionName = $settings['actionName'];
        $this->templatePath = $settings['templatePath'];
        $this->dataProviders = $settings['dataProviders'];
        $this->additionalData = $settings['additionalData'];
        $this->tempPath = PATH_site . $settings['tempPath'];
    }

    /**
     * Runs the compiling process and returns the rendered html
     *
     * @return string
     */
    public function compile()
    {
        $renderer = $this->getRenderer();
        $data = $this->getData();
        return $renderer($data);
    }

    /**
     *
     */
    public function getData()
    {
        $data = [];

        foreach ($this->settings['dataProviders'] as $dataProviderClass) {
            /** @var DataProviderInterface $dataProvider */
            $dataProvider = GeneralUtility::makeInstance($dataProviderClass, $this->settings);
            $data = array_merge_recursive($data, $dataProvider->provide());
        }

        return array_merge_recursive($data, $this->additionalData);
    }

    /**
     * Compiles the template, stores the output in a cache-file, and returns its callable content
     *
     * @return callable
     */
    public function getRenderer()
    {
        $compileFileNameAndPath = $this->getCompiledFileNameAndPath();

        if (!is_file($compileFileNameAndPath) || $this->isBackendUserOnline()) { // if we have a BE login always compile the template

            // Compiling to PHP Code
            $compiledCode = LightnCandy::compile($this->getTemplateCode(), $this->getOptions());

            // Save the compiled PHP code into a php file
            file_put_contents($compileFileNameAndPath, '<?php ' . $compiledCode . '?>');
        }

        // Returning the callable php file
        return include($compileFileNameAndPath);
    }

    protected function getOptions()
    {
        return [
            // Definition of flags (Docs: https://github.com/zordius/lightncandy#compile-options)
            'flags' => LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_RUNTIMEPARTIAL,
            // Provisioning of custom helpers
            'helpers' => [
                'json' => function ($context) {
                    return json_encode($context, JSON_HEX_APOS);
                },
                'lookup' => function ($labels, $key) {
                    return isset($labels[$key]) ? $labels[$key] : '';
                }
            ],
            // Registration of a partial-resolver to provide support for partials
            'partialresolver' => function ($cx, $name) {
                return $this->getPartialCode($cx, $name);
            }
        ];
    }

    /**
     * Returns the content of the current template file
     *
     * @return string
     */
    protected function getTemplateCode()
    {
        return file_get_contents($this->getTemplateFileNameAndPath());
    }

    /**
     * Returns the content of a given partial
     *
     * @param $cx
     * @param $name
     * @return string
     */
    protected function getPartialCode($cx, $name)
    {
        $partialContent = '';
        $partialFileNameAndPath = $this->getPartialFileNameAndPath($name);
        if (file_exists($partialFileNameAndPath)) {
            $partialContent = file_get_contents($partialFileNameAndPath);
        }
        return $partialContent;
    }

    /**
     * Returns the filename and path of the cache file
     *
     * @return string
     */
    protected function getCompiledFileNameAndPath()
    {
        // Creates the directory if not existing
        if (!is_dir($this->tempPath)) {
            GeneralUtility::mkdir_deep($this->tempPath);
        }

        $templateFileNameAndPath = $this->getTemplateFileNameAndPath();
        $fileTimeStamp = filemtime($templateFileNameAndPath);

        return $this->tempPath . basename($templateFileNameAndPath) . $fileTimeStamp . '.php';
    }

    /**
     * Returns the template filename and path
     *
     * @return string
     */
    protected function getTemplateFileNameAndPath()
    {
        return GeneralUtility::getFileAbsFileName($this->templatePath);
    }

    /**
     * Returns filename and path for a given partial name.
     * 1. Lookup below partialsRootPath
     * 2. Lookup below templatesRootPath
     *
     * @param $name
     * @return string
     */
    protected function getPartialFileNameAndPath($name)
    {
        $fileName = $name . '.hbs';
        $absFileNameAndPath = GeneralUtility::getFileAbsFileName($this->settings['partialsRootPath'] . $fileName);

        if (!$absFileNameAndPath) {
            $absFileNameAndPath = GeneralUtility::getFileAbsFileName($this->settings['templatesRootPath'] . $fileName);
        }

        return $absFileNameAndPath;
    }

    /**
     * Returns backend user online status
     * @return bool
     */
    protected function isBackendUserOnline()
    {
        return $this->getBackendUser() && (int)$this->getBackendUser()->user['uid'] > 0;
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }


}