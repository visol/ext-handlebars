<?php

namespace JFB\Handlebars\ViewHelpers;

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

class RenderViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, array $settings, array $data = []): string
    {
        /** @var HandlebarsView $handlebarsView */
        $handlebarsView = GeneralUtility::makeInstance(HandlebarsView::class);
        $handlebarsView->setControllerContext($this->controllerContext);

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
                LabelDataProvider::class,
                TyposcriptDataProvider::class
            ],
            'templatePath' => $settings['templatesRootPath'] . $template,
            'additionalData' => $data
        ]);

        $handlebarsView->assign('settings', $settings);

        return $handlebarsView->render();
    }
}
