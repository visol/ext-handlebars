<?php

namespace Visol\Handlebars\Rendering;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class RenderingContext
{
    protected ?Request $request;

    public function __construct(?Request $request)
    {
        $this->request = $request;
    }
    
    public function getExtensionKey(): ?string
    {
        return $this->request->getControllerExtensionKey();
    }

    public function getControllerName(): ?string
    {
        return $this->request->getControllerName();
    }

    public function getActionName(): ?string
    {
        return $this->request->getControllerActionName();
    }

    public function getUriBuilder(): UriBuilder
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder;
    }
}
