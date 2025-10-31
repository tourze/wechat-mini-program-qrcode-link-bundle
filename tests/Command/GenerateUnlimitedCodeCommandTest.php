<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Command\GenerateUnlimitedCodeCommand;

/**
 * @internal
 */
#[CoversClass(GenerateUnlimitedCodeCommand::class)]
#[RunTestsInSeparateProcesses]
final class GenerateUnlimitedCodeCommandTest extends AbstractCommandTestCase
{
    private Account $testAccount;

    protected function onSetUp(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('request')->willReturn('fake-qrcode-data');

        self::getContainer()->set(Client::class, $mockClient);

        // 创建测试用的Account对象，避免在每个测试方法中重复
        $this->testAccount = new Account();
        $this->testAccount->setName('测试小程序');
        $this->testAccount->setAppId('test-app-id');
        $this->testAccount->setAppSecret('test_app_secret');
        $this->testAccount->setValid(true);

        $this->persistAndFlush($this->testAccount);
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(GenerateUnlimitedCodeCommand::class);

        return new CommandTester($command);
    }

    public function testArgumentAccountId(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testArgumentPath(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testArgumentScene(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene-value',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testArgumentEnv(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
            'env' => 'trial',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testArgumentWidth(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
            'width' => '1000',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testArgumentOutput(): void
    {
        $outputPath = sys_get_temp_dir() . '/test-qrcode-' . uniqid() . '.png';

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'accountId' => $this->testAccount->getId(),
            'path' => 'pages/test/index',
            'scene' => 'test-scene',
            'output' => $outputPath,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('成功写入文件', $commandTester->getDisplay());
        $this->assertFileExists($outputPath);

        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }
}
