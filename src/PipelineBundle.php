<?php

namespace KleytonSantos\Pipeline;

use KleytonSantos\Pipeline\DependencyInjection\PipelineExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PipelineBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new PipelineExtension();
    }
}
