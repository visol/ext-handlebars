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
use JFB\Handlebars\Exception\NoTemplateConfiguredException;
use JFB\Handlebars\Exception\TemplateNotFoundException;
use JFB\Handlebars\HelperRegistry;
use LightnCandy\LightnCandy;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
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

    protected ?string $templatesRootPath;

    protected ?string $partialsRootPath;

    protected ?string $template;
    
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
        $this->templatesRootPath = $settings['templatesRootPath'] ?? null;
        $this->partialsRootPath = $settings['partialsRootPath'] ?? null;
        $this->template = $settings['template'] ?? $settings['templatePath'] ?? null;
        $this->dataProviders = $settings['dataProviders'];
        $this->additionalData = $settings['additionalData'];
        $this->tempPath = Environment::getPublicPath() . '/' . $settings['tempPath'];
    }

    /**
     * Runs the compiling process and returns the rendered html
     *
     * @return string
     */
    public function compile(): string
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

        if (!isset($this->settings['dataProviders'])) {
            $this->settings['dataProviders'] = $this->getDefaultDataProviders();
        }

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
    public function getRenderer(): callable
    {
        if (!isset($this->template)) {
            throw new NoTemplateConfiguredException('No template configured for HandlebarsEngine');
        }
        return $this->getRendererForTemplate($this->template);
    }

    public function getRendererForTemplate(string $template): callable
    {
        $templatePathAndFilename = $this->getTemplatePathAndFilename($template);
        if (!isset($templatePathAndFilename)) {
            throw new TemplateNotFoundException($template, $this->templatesRootPath);
        }
        
        $compiledCodePathAndFilename = $this->getCompiledCodePathAndFilename($templatePathAndFilename);

        if (!is_file($compiledCodePathAndFilename) || $this->isBackendUserOnline()) { // if we have a BE login always compile the template

            // Compiling to PHP Code
            $compiledCode = LightnCandy::compile($this->getTemplateCode($templatePathAndFilename), $this->getOptions());

            // Save the compiled PHP code into a php file
            file_put_contents($compiledCodePathAndFilename, '<?php ' . $compiledCode . '?>');
        }

        // Returning the callable php file
        return include($compiledCodePathAndFilename);
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        $helpers = $this->getViewHelpers();

        return [
            // Definition of flags (Docs: https://github.com/zordius/lightncandy#compile-options)
            'flags' => LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_RUNTIMEPARTIAL,
            // Provisioning of custom helpers
            'helpers' => $helpers,
            // Registration of a partial-resolver to provide support for partials
            'partialresolver' => function ($cx, $name) {
                return $this->getPartialCode($cx, $name);
            }
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultHelpers(): array
    {
        return [
            'content' => function ($context) {
                // TODO: Implement content and matching block helper, see https://github.com/shannonmoeller/handlebars-layouts
                throw new \Exception('Handlebars block/content helpers not implemented (see https://github.com/shannonmoeller/handlebars-layouts).', 1497617391);
            },
            'block' => function ($context) {
                // TODO: Implement block and matching content helper, see https://github.com/shannonmoeller/handlebars-layouts
                return $context;
            },
            'json' => function ($context) {
                return json_encode($context, JSON_HEX_APOS);
            },
            'lookup' => function ($labels, $key) {
                return isset($labels[$key]) ? $labels[$key] : '';
            }
        ];
    }

    /**
     * Returns the content of the current template file
     *
     * @return string
     */
    protected function getTemplateCode($templatePathAndFilename): string
    {
        return file_get_contents($templatePathAndFilename);
    }

    /**
     * Returns the content of a given partial
     *
     * @param $cx
     * @param $name
     * @return string
     */
    protected function getPartialCode($cx, $name): string
    {
        $partialContent = '';
        $partialFileNameAndPath = $this->getPartialPathAndFileName($name);
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
    protected function getCompiledCodePathAndFileName(string $templatePathAndFilename): string
    {
        // Creates the directory if not existing
        if (!is_dir($this->tempPath)) {
            GeneralUtility::mkdir_deep($this->tempPath);
        }

        $basename = basename($templatePathAndFilename);
        $timeStamp = filemtime($templatePathAndFilename);

        return $this->tempPath . $basename . '_' . $timeStamp . '_' . sha1($templatePathAndFilename) . '.php';
    }

    /**
     * Returns the template filename and path
     *
     * @param string $template
     * @return string
     */
    protected function getTemplatePathAndFilename(string $template): ?string
    {
        $candidates = [$template];
        if (isset($this->templatesRootPath)) {
            $candidates[] = $this->templatesRootPath . $template;
        }
        
        return $this->findHbsFile($candidates);
    }

    /**
     * Returns filename and path for a given partial name.
     * 1. Lookup below partialsRootPath
     * 2. Lookup below templatesRootPath
     *
     * @param string $name
     * @return string|null
     */
    protected function getPartialPathAndFileName(string $name): ?string
    {
        $candidates = [$name];
        if (isset($this->partialsRootPath)) {
            $candidates[] = $this->partialsRootPath . $name;
        }
        if (isset($this->templatesRootPath)) {
            $candidates[] = $this->templatesRootPath . $name;
        }
        
        return $this->findHbsFile($candidates);
    }

    protected function findHbsFile(array $basenameCandidates): ?string
    {
        foreach ($basenameCandidates as $basenameCandidate) {
            $candidates = [
                $basenameCandidate,
                $basenameCandidate . '.hbs'
            ];
            
            foreach($candidates as $candidate) {
                $pathAndFilename = GeneralUtility::getFileAbsFileName($candidate);
                if (is_file($pathAndFilename)) {
                    return $pathAndFilename;
                }
            }
        }

        return null;
    }
    
    /**
     * Returns backend user online status
     * @return bool
     */
    protected function isBackendUserOnline(): bool
    {
        return $this->getBackendUser() && (int)$this->getBackendUser()->user['uid'] > 0;
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication|null
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getDefaultDataProviders()
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $defaultDataProviders = (array)$extensionConfiguration->get('handlebars', 'defaultDataProviders');
        return $defaultDataProviders;
    }

    protected function getViewHelpers(): array
    {
        $helpers = array_merge(
            $this->getDefaultHelpers(),
            HelperRegistry::getInstance()->getHelpers()
        );
        array_walk($helpers, fn($helperFunction) => \Closure::bind($helperFunction, $this, $this));
        return $helpers;
    }
}
