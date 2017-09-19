<?php
namespace JFB\Handlebars;

/*
 * This file is part of the JFB/Handlebars project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Closure;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper registry module.
 */
class HelperRegistry implements SingletonInterface
{

    /**
     * @var array
     */
    protected $helpers = [];

    /**
     * Returns a class instance.
     *
     * @return object|HelperRegistry
     */
    public static function getInstance(): HelperRegistry
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Register a new helper.
     *
     * @param string $name
     * @param Closure $helper
     * @return $this
     */
    public function register($name, Closure $helper)
    {
        $this->helpers[$name] = $helper;
        return $this;
    }

    /**
     * Un-Register a helper
     *
     * @param string $name
     * @return $this
     */
    public function unRegister($name)
    {
        unset($this->helpers[$name]);
        return $this;
    }

    /**
     * @return array
     */
    public function getHelpers(): array
    {
        return $this->helpers;
    }

}
