<?php
namespace JFB\Handlebars\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use JFB\Handlebars\Engine\HandlebarsEngine;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;

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
class HandlebarsView extends AbstractView
{

    protected $template = null;

    /**
     * Render method of the view (entry point)
     *
     * @return mixed
     */
    public function render()
    {
        /** @var HandlebarsEngine $handlebarsEngine */
        $handlebarsEngine = GeneralUtility::makeInstance(
            HandlebarsEngine::class
        );

        $handlebarsEngine->setTemplateFileNameAndPath($this->template);
        $handlebarsEngine->setVariables($this->variables);

        return $handlebarsEngine->compile();
    }

    /**
     * @return null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param null $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function resetVariables()
    {
        $this->variables = [];
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function applyFormatter()
    {

    }

    /**
     * Returns context variables as array
     *
     * @return array
     */
    protected function getContextVariables()
    {
        return [
            'extensionKey' => strtolower($this->controllerContext->getRequest()->getControllerExtensionKey()),
            'controllerName' => strtolower($this->controllerContext->getRequest()->getControllerName()),
            'actionName' => strtolower($this->controllerContext->getRequest()->getControllerActionName()),
        ];
    }
}