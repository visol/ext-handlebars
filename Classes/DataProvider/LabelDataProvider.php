<?php
namespace JFB\Handlebars\DataProvider;

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

use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LabelDataProvider
 */
class LabelDataProvider extends AbstractDataProvider
{

    /**
     * Entry point
     *
     * @return array
     */
    public function provide()
    {
        return array('labels' => $this->getLabels());
    }

    /**
     * Get all locallang labels of the current extension which are prefixed with current controller and action
     * e.g. 'controllerName.actionName.title'
     *
     * @return array
     */
    protected function getLabels()
    {
        $extensionKey = $this->settings['extensionKey'];
        $controllerName = $this->settings['controllerName'];
        $actionName = $this->settings['actionName'];

        $labels = LocalizationUtility::getLabels($extensionKey, $controllerName . '.' . $actionName);
        foreach ($labels as $key => $label) {
            $formattedKey = str_replace($controllerName . '.' . $actionName . '.', '', $key);
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
}