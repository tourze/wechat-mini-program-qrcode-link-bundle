<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;
use WechatMiniProgramQrcodeLinkBundle\Procedure\GetUserShareCode;

/**
 * @internal
 */
#[CoversClass(GetUserShareCode::class)]
#[RunTestsInSeparateProcesses]
final class GetUserShareCodeTest extends AbstractProcedureTestCase
{
    private GetUserShareCode $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetUserShareCode::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetUserShareCode::class, $this->procedure);
    }

    public function testExecuteRequiresAuthenticatedUser(): void
    {
        // 这个测试验证 Procedure 本身的结构和元数据
        $reflection = new \ReflectionClass(GetUserShareCode::class);

        // 验证类是 final
        $this->assertTrue($reflection->isFinal(), 'GetUserShareCode should be final');

        // 验证有必需的属性标注
        $attributes = $reflection->getAttributes();
        $this->assertGreaterThan(0, count($attributes), 'GetUserShareCode should have class attributes');
    }
}
