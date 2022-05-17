<?php

namespace JFB\Handlebars\Rendering;

class RenderingContext
{
    protected ?string $extensionKey = null;
    protected ?string $controllerName = null;
    protected ?string $actionName = null;

    public function __construct(?string $extensionKey, ?string $controllerName, ?string $actionName)
    {
        $this->extensionKey = $extensionKey;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
    }
    
    public function getExtensionKey(): ?string
    {
        return $this->extensionKey;
    }

    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }
}
