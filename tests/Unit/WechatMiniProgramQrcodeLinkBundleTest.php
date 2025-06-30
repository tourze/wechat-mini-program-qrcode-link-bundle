<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WechatMiniProgramQrcodeLinkBundle\WechatMiniProgramQrcodeLinkBundle;

class WechatMiniProgramQrcodeLinkBundleTest extends TestCase
{
    public function testItIsSymfonyBundle(): void
    {
        $bundle = new WechatMiniProgramQrcodeLinkBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testItHasCorrectName(): void
    {
        $bundle = new WechatMiniProgramQrcodeLinkBundle();
        $this->assertSame('WechatMiniProgramQrcodeLinkBundle', $bundle->getName());
    }

    public function testItCanBeCreated(): void
    {
        $bundle = new WechatMiniProgramQrcodeLinkBundle();
        $this->assertNotNull($bundle);
    }
}