<?php

namespace Visol\Handlebars\ViewHelpers;

use Visol\Handlebars\Rendering\RenderingContext;
use Visol\Handlebars\View\HandlebarsView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
 *
 */


class RenderViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('template', 'string', '', true);
        $this->registerArgument('settings', 'array', '', false, []);
        $this->registerArgument('data', 'array', '', false, []);
    }
    
    public function render(): string
    {
        $template = $this->arguments['template'];
        $settings = $this->arguments['settings'];
        $data = $this->arguments['data'];
        
        $handlebarsView = GeneralUtility::makeInstance(HandlebarsView::class);
        $handlebarsRenderingContext = GeneralUtility::makeInstance(
            RenderingContext::class,
            $this->renderingContext->getRequest()
        );
        $handlebarsView->setRenderingContext($handlebarsRenderingContext);

        if (isset($settings['handlebars']) && is_array($settings['handlebars'])) {
            $settings = $settings['handlebars'];
        } else {
            $settings = [];
        }
 
        $settings = array_replace_recursive($settings, [
            'templatesRootPath' => $settings['templatesRootPath'],
            'template' => $template,
            'additionalData' => $data
        ]);

        $handlebarsView->assign('settings', $settings);

        return $handlebarsView->render();
    }
}
