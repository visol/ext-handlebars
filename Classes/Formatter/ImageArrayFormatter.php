<?php

namespace JFB\Handlebars\Formatter;

/*
 * This file is part of the JFB/Handlebars project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class ImageArrayFormatter extends AbstractFormatter
{

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ImageService $imageService
     */
    public function injectImageService(\TYPO3\CMS\Extbase\Service\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface|\TYPO3\CMS\Core\Resource\AbstractFile $image
     * @param array $imageSizes
     * @param int $ratio
     * @param bool $crop
     * @param string $defaultImage
     * @param string $alt
     * @return array
     */
    public function format($image, $imageSizes = [800, 1280, 2048], $ratio = 16/10, $crop = true, $defaultImage = null, $alt = null)
    {
        if (is_null($image) && $defaultImage) {
            $imageObj = $this->imageService->getImage($defaultImage, null, 1);
        } elseif (is_callable([$image, 'getOriginalResource'])) {
            $imageObj = $this->imageService->getImage($image, $image, 1);
        } elseif (is_string($image)) {
            $imageObj = $this->imageService->getImage($image, null, 1);
        }

        if ($imageObj) {
            if ($imageObj->getExtension() === 'svg') {
                $imageArray['alt'] = $alt;
                $imageArray['src'] = $imageObj->getIdentifier();
            } else {
                $imageArray['alt'] = ($alt) ? $alt : $imageObj->getProperty('alternative');
                foreach ($imageSizes as $size) {
                    $cropParam = ($crop)?'c':'';
                    $processingInstructions = [
                        'width' => $size . $cropParam,
                        'height' => $size / ($ratio) . $cropParam,
                    ];
                    $processedImage = $this->imageService->applyProcessingInstructions($imageObj, $processingInstructions);

                    $sizeList[] = [$size];

                    $urlList[] = [ $this->imageService->getImageUri($processedImage) ];
                }

                $imageArray['srcset']['sizeList'] = $sizeList;

                if (count($urlList) > 0) {
                    $imageArray['srcset']['urlList'] = $urlList;
                }
            }
            return $imageArray;
        }
    }
}
