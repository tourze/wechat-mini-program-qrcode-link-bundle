<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatMiniProgramQrcodeLinkBundle\WechatMiniProgramQrcodeLinkBundle;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramQrcodeLinkBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramQrcodeLinkBundleTest extends AbstractBundleTestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        /** @phpstan-ignore-next-line Bundle不是服务，只能直接实例化 */
        $bundle = new WechatMiniProgramQrcodeLinkBundle();

        $this->assertSame('WechatMiniProgramQrcodeLinkBundle', $bundle->getName());
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = WechatMiniProgramQrcodeLinkBundle::getBundleDependencies();

        $this->assertIsArray($dependencies);
        $this->assertNotEmpty($dependencies);
        $this->assertArrayHasKey('all', $dependencies[array_key_first($dependencies)]);
    }
}
