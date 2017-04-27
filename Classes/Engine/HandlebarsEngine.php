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

use LightnCandy\LightnCandy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class HandlebarsEngine
 */
class HandlebarsEngine
{
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
    protected $tempPath;

    /**
     * HandlebarsEngine constructor.
     *
     * @param $extensionKey string
     * @param $controllerName string
     * @param $actionName string
     * @param $settings array
     */
    public function __construct($extensionKey, $controllerName, $actionName, $settings)
    {
        $this->extensionKey = $extensionKey;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->settings = $settings;
        $this->tempPath = PATH_site . $settings['tempPath'];
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

            $templateCode = $this->getTemplateCode();

            // Definition of flags (Docs: https://github.com/zordius/lightncandy#compile-options)
            $options['flags'] = LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_RUNTIMEPARTIAL;

            // Provisioning of custom helpers
            $options['helpers'] = [
                'json' => function ($context) {
                    return json_encode($context);
                },
                'lookup' => function ($labels, $key) {
                    return isset($labels[$key]) ? $labels[$key] : '';
                }
            ];

            // Registration of a partial-resolver to provide support for partials
            $options['partialresolver'] = function ($cx, $name) {
                return $this->getPartialCode($cx, $name);
            };

            // Compiling to PHP Code
            $compiledCode = LightnCandy::compile($templateCode, $options);

            // Save the compiled PHP code into a php file
            file_put_contents($compileFileNameAndPath, '<?php ' . $compiledCode . '?>');
        }

        // Returning the callable php file
        return include($compileFileNameAndPath);
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
    protected function getPartialCode ($cx, $name) {
        $partialContent = '';
        $partialFileNameAndPath = $this->getPartialFileNameAndPath($name);
        if (file_exists($partialFileNameAndPath)) {
            $partialContent = file_get_contents($partialFileNameAndPath);
        }
        return $partialContent;
    }

    /**
     * Returns the filename of the cache file by naming convention:
     * tempPath/controllerName/actionName+timestamp.php
     *
     * @return string
     */
    protected function getCompiledFileNameAndPath()
    {
        $path = $this->tempPath . $this->controllerName . '/';

        // Creates the directory if not existing
        if (!is_dir($path)) {
            GeneralUtility::mkdir_deep($path);
        }

        $templateFileNameAndPath = $this->getTemplateFileNameAndPath();
        $fileTimeStamp = filemtime($templateFileNameAndPath);

        return $path . $this->actionName . $fileTimeStamp . '.php';
    }

    /**
     * Returns the template filename and path by naming convention:
     * templatesRootPath/controllerName_actionName/controllerName_actionName.fileExtension
     *
     * @return string
     */
    protected function getTemplateFileNameAndPath()
    {
        $name = $this->controllerName . '_' . $this->actionName;
        $path = $this->settings['handlebars']['templatesRootPath'] . $name . '/' . $name . $this->settings['handlebars']['fileExtension'];;
        return GeneralUtility::getFileAbsFileName($path);
    }

    /**
     * Returns filename and path for a given partial name:
     * partialsRootPath/name.fileExtension
     *
     * @param $name
     * @return string
     */
    protected function getPartialFileNameAndPath($name) {
        $path = $this->settings['handlebars']['partialsRootPath'] . $name . $this->settings['handlebars']['fileExtension'];
        return GeneralUtility::getFileAbsFileName($path);
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