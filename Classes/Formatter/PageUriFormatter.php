<?php

namespace JFB\Handlebars\Formatter;

/*
 * This file is part of the JFB/Handlebars project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class PageUriFormatter extends AbstractFormatter
{

    /**
     * @param int $pageUid target page. See TypoLink destination
     * @param array $arguments
     * @param int $pageType type of the target page. See typolink.parameter
     * @param string $section the anchor to be added to the URI
     * @param int $useCacheHash add cHash param to url
     * @return array
     */
    public function format($pageUid, $arguments = [], $pageType = 0, $section = '', $useCacheHash = 1)
    {
        $uriBuilder = $this->getControllerContext()->getUriBuilder();
        $uri = $uriBuilder
            ->setTargetPageUid($pageUid)
            ->setArguments($arguments)
            ->setTargetPageType($pageType)
            ->setSection($section)
            ->setUseCacheHash($useCacheHash)
            ->build();
        return $uri;
    }
}
