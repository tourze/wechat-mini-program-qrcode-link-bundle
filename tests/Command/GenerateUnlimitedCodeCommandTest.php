<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Command\GenerateUnlimitedCodeCommand;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;

class GenerateUnlimitedCodeCommandTest extends TestCase
{
    private MockObject|AccountRepository $accountRepository;
    private MockObject|Client $client;
    private GenerateUnlimitedCodeCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->client = $this->createMock(Client::class);
        
        $this->command = new GenerateUnlimitedCodeCommand(
            $this->accountRepository,
            $this->client
        );
        
        $application = new Application();
        $application->add($this->command);
        
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute_WithValidInput(): void
    {
        // 准备模拟账户
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(1);
        $account->method('getAppId')->willReturn('test-app-id');
        
        // 设置 AccountRepository 返回模拟账户
        $this->accountRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($account);
        
        // 模拟 Client 返回二维码图像数据
        $qrCodeData = 'fake-qrcode-data';
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with($this->callback(function (CodeUnLimitRequest $request) use ($account) {
                $this->assertSame($account, $request->getAccount());
                $this->assertEquals('test-scene', $request->getScene());
                $this->assertEquals('pages/test/index', $request->getPage());
                $this->assertFalse($request->isCheckPath());
                $this->assertEquals('release', $request->getEnvVersion());
                $this->assertEquals(750, $request->getWidth());
                return true;
            }))
            ->willReturn($qrCodeData);
        
        // 执行命令
        $this->commandTester->execute([
            'accountId' => 1,
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
        ]);
        
        // 验证命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
    
    public function testExecute_WithOutputPath(): void
    {
        // 创建临时文件用于测试输出
        $outputPath = sys_get_temp_dir() . '/test-qrcode-' . uniqid() . '.png';
        
        // 准备模拟账户
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(1);
        $account->method('getAppId')->willReturn('test-app-id');
        
        // 设置 AccountRepository 返回模拟账户
        $this->accountRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($account);
        
        // 模拟 Client 返回二维码图像数据
        $qrCodeData = 'fake-qrcode-data';
        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturn($qrCodeData);
        
        // 执行命令
        $this->commandTester->execute([
            'accountId' => 1,
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
            'output' => $outputPath,
        ]);
        
        // 验证命令执行成功并写入文件
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('成功写入文件', $this->commandTester->getDisplay());
        
        // 验证文件内容正确
        $this->assertFileExists($outputPath);
        $this->assertEquals('fake-qrcode-data', file_get_contents($outputPath));
        
        // 清理测试文件
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }
    
    public function testExecute_WithCustomEnvAndWidth(): void
    {
        // 准备模拟账户
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(1);
        $account->method('getAppId')->willReturn('test-app-id');
        
        // 设置 AccountRepository 返回模拟账户
        $this->accountRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($account);
        
        // 模拟 Client 返回二维码图像数据
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with($this->callback(function (CodeUnLimitRequest $request) {
                $this->assertEquals('trial', $request->getEnvVersion());
                $this->assertEquals(1000, $request->getWidth());
                return true;
            }))
            ->willReturn('fake-qrcode-data');
        
        // 执行命令
        $this->commandTester->execute([
            'accountId' => 1,
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
            'env' => 'trial',
            'width' => '1000',
        ]);
        
        // 验证命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
    
    public function testExecute_WithInvalidAccount(): void
    {
        // 设置 AccountRepository 返回 null，表示找不到账户
        $this->accountRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        // 验证命令抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('找不到小程序');
        
        // 执行命令
        $this->commandTester->execute([
            'accountId' => 999,
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
        ]);
    }
    
    public function testExecute_WithPathWithLeadingSlash(): void
    {
        // 准备模拟账户
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(1);
        $account->method('getAppId')->willReturn('test-app-id');
        
        // 设置 AccountRepository 返回模拟账户
        $this->accountRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($account);
        
        // 模拟 Client 返回二维码图像数据
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with($this->callback(function (CodeUnLimitRequest $request) {
                // 验证路径处理，去除了前导斜杠
                $this->assertEquals('pages/test/index', $request->getPage());
                return true;
            }))
            ->willReturn('fake-qrcode-data');
        
        // 执行命令
        $this->commandTester->execute([
            'accountId' => 1,
            'path' => '/pages/test/index',
            'scene' => 'test-scene',
        ]);
        
        // 验证命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
} 