<?php

namespace JFB\Handlebars\Formatter;

/*
 * This file is part of the JFB/Handlebars project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class LinkArrayFormatter extends AbstractFormatter
{

    /**
     * @param string $label
     * @param string $typolinkStringParams
     * @param int $useCacheHash
     * @param string $icon
     * @return array
     */
    public function format(string $label = null, string $typolinkStringParams = null, int $useCacheHash = 1, string $icon = null)
    {
        if ($label != null && $typolinkStringParams != null) {
            $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
            $typolinkConfiguration = $typoLinkCodec->decode($typolinkStringParams);

            if ($typolinkConfiguration) {
                $typolinkConf = [
                    'parameter' => $typolinkStringParams,
                    'useCacheHash' => $useCacheHash,
                    'returnLast' => 'url',
                ];

                /** @var ContentObjectRenderer $contentObject */
                $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                $contentObject->start([], '');
                $url = $contentObject->typoLink($label, $typolinkConf);
            }

            if ($icon) {
                $linkArray = [
                    'ariaLabel' => $label,
                    'url' => $url,
                    'title' => $typolinkConfiguration['title'],
                    'target' => $typolinkConfiguration['target'],
                    'icon' => $icon,
                ];
            } else {
                $linkArray = [
                    'label' => $label,
                    'url' => $url,
                    'title' => $typolinkConfiguration['title'],
                    'target' => $typolinkConfiguration['target'],
                ];
            }

            return $linkArray;
        }
    }
}
