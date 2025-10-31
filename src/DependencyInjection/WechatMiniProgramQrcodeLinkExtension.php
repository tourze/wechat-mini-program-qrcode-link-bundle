<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class WechatMiniProgramQrcodeLinkExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
