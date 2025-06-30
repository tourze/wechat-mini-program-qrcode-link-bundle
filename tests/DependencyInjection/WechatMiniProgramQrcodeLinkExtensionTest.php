<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatMiniProgramQrcodeLinkBundle\DependencyInjection\WechatMiniProgramQrcodeLinkExtension;

class WechatMiniProgramQrcodeLinkExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new WechatMiniProgramQrcodeLinkExtension();
        
        $extension->load([], $container);
        
        // 验证服务是否被正确加载
        self::assertTrue($container->hasDefinition('WechatMiniProgramQrcodeLinkBundle\Command\GenerateUnlimitedCodeCommand'));
        self::assertTrue($container->hasDefinition('WechatMiniProgramQrcodeLinkBundle\Procedure\GetUserShareCode'));
        self::assertTrue($container->hasDefinition('Tourze\FileNameGenerator\RandomNameGenerator'));
    }
}