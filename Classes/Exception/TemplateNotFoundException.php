<?php

namespace Visol\Handlebars\Exception;

class TemplateNotFoundException extends \RuntimeException
{
    public function __construct(string $template, ?string $templatesRootPath)
    {
        parent::__construct(sprintf(
            'Template %s not found with templatesRootPath %s',
            $template,
            $templatesRootPath ?? 'null'
        ));
    }
}
