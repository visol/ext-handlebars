<?php
namespace Visol\Handlebars\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use Visol\Handlebars\Rendering\HandlebarsContext;
use Visol\Handlebars\Engine\HandlebarsEngine;

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
class HandlebarsView implements ViewInterface
{
    protected HandlebarsContext $renderingContext;

    protected array $variables = [];

    /**
     * Render method of the view (entry point)
     */
    public function render(?string $templateFileName = null): string
    {
        $settings = $this->variables['settings'];
        $settings = array_replace_recursive($settings, $this->getContextVariables());
        $handlebarsEngine = GeneralUtility::makeInstance(
            HandlebarsEngine::class,
            $settings
        );

        return $handlebarsEngine->compile();
    }

    protected function getContextVariables(): array
    {
        return [
            'extensionKey' => strtolower($this->renderingContext->getExtensionKey()),
            'controllerName' => strtolower($this->renderingContext->getControllerName()),
            'actionName' => strtolower($this->renderingContext->getActionName()),
        ];
    }

    /**
     * Add a variable to $this->viewData.
     * Can be chained, so $this->view->assign(..., ...)->assign(..., ...); is possible
     *
     * @param string $key Key of variable
     * @param mixed $value Value of object
     * @return self an instance of $this, to enable chaining
     */
    public function assign(string $key, mixed $value): ViewInterface
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Add multiple variables to $this->viewData.
     *
     * @param array $values array in the format array(key1 => value1, key2 => value2).
     * @return self an instance of $this, to enable chaining
     */
    public function assignMultiple(array $values): ViewInterface
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }
        return $this;
    }

    public function setRenderingContext(HandlebarsContext $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }
}
