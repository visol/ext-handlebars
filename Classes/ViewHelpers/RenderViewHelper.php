<?php

namespace JFB\Handlebars\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use JFB\Handlebars\DataProvider\LabelDataProvider;
use JFB\Handlebars\DataProvider\TyposcriptDataProvider;
use JFB\Handlebars\View\HandlebarsView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Jonas Renggli <jonas.renggli@visol.ch>
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

/**
 * == Examples ==
 *
 * Don't forget to set your TS configuration in the loaded plugin if needed
 *
 * settings.handlebars < lib.handlebars
 *
 * <code title="Default parameters">
 * <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
 *       xmlns:h="http://typo3.org/ns/JFB/Handlebars/ViewHelpers"
 *       data-namespace-typo3-fluid="true"
 * >
 *   <h:render template="modules/introduction/introduction.hbs" settings="{settings}" data="{
 *       headline: display_name,
 *       headlineTag: 'h2'
 *   }" />
 * </html>
 * </code>
 */
class RenderViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('template', 'string', 'template path', true);
        $this->registerArgument('settings', 'string', 'extension settings', true);
        $this->registerArgument('data', 'array', 'Passed data array');
    }

    /**
     * @param string $template
     * @param array $settings
     * @param array $data
     * @return string
     */
    public function render(): string
    {
        $template = $this->arguments['template'];
        $settings = $this->arguments['settings'];
        $data = $this->arguments['data'];

        /** @var HandlebarsView $handlebarsView */
        $handlebarsView = GeneralUtility::makeInstance(HandlebarsView::class);

        // @todo fix for TYPO3 v8 can be removed later
        if ($this->renderingContext->getControllerContext() !== null) {
            $controllerContext = $this->renderingContext->getControllerContext();
        } elseif (method_exists($this->renderingContext, 'getControllerContext')) {
            $controllerContext = $this->renderingContext->getControllerContext();
        }
        $handlebarsView->setControllerContext($controllerContext);

        if (
            isset($settings['handlebars'])
            && is_array($settings['handlebars'])
        ) {
            $settings = $settings['handlebars'];
        } else {
            $settings = [];
        }

        $settings = array_merge_recursive($settings, [
            'dataProviders' => [
                TyposcriptDataProvider::class
            ],
            'templatePath' => $settings['templatesRootPath'] . $template,
            'additionalData' => $data
        ]);

        $handlebarsView->assign('settings', $settings);

        return $handlebarsView->render();
    }
}
