<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\FileNameGenerator\RandomNameGenerator;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Procedure\GetUserShareCode;

/**
 * @internal
 * @phpstan-ignore-next-line 这是一个JSON-RPC Procedure的单元测试，需要使用Mock对象模拟复杂的外部依赖，包括跨包服务
 */
#[CoversClass(GetUserShareCode::class)]
final class GetUserShareCodeTest extends TestCase
{
    private GetUserShareCode $procedure;

    private MockObject|AccountService $accountService;

    protected function setUp(): void
    {
        parent::setUp();

        // 只创建测试实际需要的Mock对象
        $this->accountService = $this->createMock(AccountService::class);
        $requestStack = $this->createMock(RequestStack::class);

        // 为其他依赖创建简单的Mock
        $this->procedure = new GetUserShareCode(
            $requestStack,
            $this->accountService,
            $this->createMock(Client::class),
            $this->createMock(RandomNameGenerator::class),
            $this->createMock(FilesystemOperator::class),
            $this->createMock(Security::class),
            $this->createMock(EntityManagerInterface::class)
        );

        // 设置RequestStack返回一个空的Request
        $requestStack->method('getMainRequest')->willReturn($this->createMock(Request::class));
    }

    public function testExecuteWithInvalidAppId(): void
    {
        $this->procedure->appId = 'invalid-app-id';

        $this->accountService
            ->expects($this->once())
            ->method('detectAccountFromRequest')
            ->willReturn(null)
        ;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');

        $this->procedure->execute();
    }
}
