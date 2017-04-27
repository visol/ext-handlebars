<?php
namespace JFB\Handlebars\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use JFB\Handlebars\Engine\HandlebarsEngine;
use Sinso\Translationapi\Utility\LocalizationUtility;

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
abstract class AbstractHandlebarsView extends \TYPO3\CMS\Extbase\Mvc\View\AbstractView implements HandlebarsViewInterface
{
    /**
     * Current extension key (lowercase)
     *
     * @var string
     */
    protected $extensionKey;

    /**
     * Current controller name (lowercase)
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Current action name (lowercase)
     * @var string
     */
    protected $actionName;


    /**
     * Initialize method for the view
     */
    public function initialize()
    {
        $this->extensionKey = strtolower($this->controllerContext->getRequest()->getControllerExtensionKey());
        $this->controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
        $this->actionName = strtolower($this->controllerContext->getRequest()->getControllerActionName());

        //Get global variables from typoscript configuration
        $this->variables = array_merge_recursive($this->variables, $this->variables['settings']['handlebars']['variables']);
        //Get relevant localizations from locallang file
        $this->variables = array_merge_recursive($this->variables, array('labels' => $this->getLabels()));
        //Add custom variables (meant to be provided by specific view)
        $this->variables = array_merge_recursive($this->variables, $this->addVariables());
    }

    /**
     * Render method of the view (entry point)
     *
     * @return mixed
     */
    public function render()
    {
        $this->initialize();
        $renderer = $this->getRenderer();
        return $renderer($this->variables);
    }

    /**
     * Get all locallang labels of the current extension which are prefixed with current controller and action
     * e.g. 'controllerName.actionName.title'
     *
     * @return array
     */
    protected function getLabels()
    {
        $labels = LocalizationUtility::getLabels($this->extensionKey, $this->controllerName . '.' . $this->actionName);
        foreach ($labels as $key => $label) {
            $formattedKey = str_replace($this->controllerName . '.' . $this->actionName . '.', '', $key);
            $labels[$formattedKey] = $label; // For convenience sake keep both
            $this->transformMultiDimensionalObject($labels, $formattedKey, $label);
        }

        return $labels;
    }

    /**
     * Sets a value in a nested array based on path
     * See http://stackoverflow.com/a/9628276/419887
     *
     * @param array $array The array to modify
     * @param string $path The path in the array
     * @param mixed $value The value to set
     * @return mixed
     */
    protected function transformMultiDimensionalObject(&$array, $path, $value)
    {
        $pathParts = GeneralUtility::trimExplode('.', $path);

        $current = &$array;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }

        $backup = $current;
        $current = $value;
        return $backup;
    }

    /**
     * Invokes the compiling process and returns the callable content of the compiled output
     *
     * @return callable
     */
    protected function getRenderer()
    {
        /** @var HandlebarsEngine $handlebarsEngine */
        $handlebarsEngine = GeneralUtility::makeInstance(
            HandlebarsEngine::class,
            $this->extensionKey,
            $this->controllerName,
            $this->actionName,
            $this->variables['settings']
        );
        return $handlebarsEngine->getRenderer();
    }
}