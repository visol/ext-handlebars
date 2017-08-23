<?php

namespace JFB\Handlebars\Formatter;

/*
 * This file is part of the JFB/Handlebars project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

abstract class AbstractFormatter
{
    /** @var ControllerContext */
    protected $controllerContext;

    /**
     * @return ControllerContext
     */
    public function getControllerContext(): ControllerContext
    {
        return $this->controllerContext;
    }

    /**
     * @param ControllerContext $controllerContext
     */
    public function setControllerContext(ControllerContext $controllerContext)
    {
        $this->controllerContext = $controllerContext;
    }

    protected function getTranslationsItems($extensionKey, $prefix)
    {
        if (TYPO3_MODE === 'FE') {
            $languageKey = $GLOBALS['TSFE']->lang;
        } else {
            $languageKey = $GLOBALS['LANG']->lang;
        }

        $labels = LocalizationUtility::getLabels($extensionKey, $prefix, $languageKey);
        $labels = LocalizationUtility::stripPrefix($labels, $prefix);
        $labels = LocalizationUtility::expandKeys($labels);

        return $labels;
    }

    protected function getTranslation($extensionName, $key, $arguments = null)
    {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $extensionName, $arguments);
    }
}
