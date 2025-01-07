<?php

namespace Visol\Handlebars\Rendering;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class HandlebarsContext
{
    protected ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
    
    public function getExtensionKey(): ?string
    {
        if (!$this->request instanceof \TYPO3\CMS\Extbase\Mvc\RequestInterface) {
            return null;
        }

        return $this->request->getControllerExtensionKey();
    }

    public function getControllerName(): ?string
    {
        if (!$this->request instanceof \TYPO3\CMS\Extbase\Mvc\RequestInterface) {
            return null;
        }

        return $this->request->getControllerName();
    }

    public function getActionName(): ?string
    {
        if (!$this->request instanceof \TYPO3\CMS\Extbase\Mvc\RequestInterface) {
            return null;
        }

        return $this->request->getControllerActionName();
    }

    public function getUriBuilder(): UriBuilder
    {
        // TODO: Implement cache
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder;
    }
}
