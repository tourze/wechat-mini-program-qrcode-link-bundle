<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Exception\AccountNotFoundException;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;

#[AsCommand(name: self::NAME, description: '生成指定路径和场景值的码')]
class GenerateUnlimitedCodeCommand extends Command
{
    public const NAME = 'wechat-mini-program:generate-unlimited-code';

    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly Client $client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('accountId', InputArgument::REQUIRED, '小程序ID')
            ->addArgument('path', InputArgument::REQUIRED, '路径')
            ->addArgument('scene', InputArgument::REQUIRED, '场景值')
            ->addArgument('env', InputArgument::OPTIONAL, '打开环境', 'release')
            ->addArgument('width', InputArgument::OPTIONAL, '尺寸', '750')
            ->addArgument('output', InputArgument::OPTIONAL, '保存路径')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $this->findAccount($input);
        $request = $this->createRequest($input, $account);
        $png = $this->client->request($request);
        if (!is_string($png)) {
            throw new \InvalidArgumentException('Expected string response from client');
        }
        $this->saveOutput($input, $output, $png);

        return Command::SUCCESS;
    }

    private function findAccount(InputInterface $input): Account
    {
        $accountId = $input->getArgument('accountId');
        if (!is_string($accountId) && !is_int($accountId)) {
            throw new \InvalidArgumentException('Account ID must be string or int');
        }

        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            throw new AccountNotFoundException('找不到小程序');
        }

        return $account;
    }

    private function createRequest(InputInterface $input, Account $account): CodeUnLimitRequest
    {
        $pathArg = $input->getArgument('path');
        if (!is_string($pathArg)) {
            throw new \InvalidArgumentException('Path must be string');
        }
        $basePath = ltrim($pathArg, '/');
        $basePath = trim($basePath);

        $sceneArg = $input->getArgument('scene');
        if (!is_string($sceneArg)) {
            throw new \InvalidArgumentException('Scene must be string');
        }

        $envArg = $input->getArgument('env');
        if (!is_string($envArg)) {
            throw new \InvalidArgumentException('Env must be string');
        }

        $widthArg = $input->getArgument('width');
        if (!is_string($widthArg) && !is_int($widthArg)) {
            throw new \InvalidArgumentException('Width must be string or int');
        }

        $request = new CodeUnLimitRequest();
        $request->setAccount($account);
        $request->setScene($sceneArg);
        $request->setPage($basePath);
        $request->setCheckPath(false);
        $request->setEnvVersion($envArg);
        $request->setWidth((int) $widthArg);

        return $request;
    }

    private function saveOutput(InputInterface $input, OutputInterface $output, string $png): void
    {
        $outputArg = $input->getArgument('output');
        if (null !== $outputArg) {
            if (!is_string($outputArg)) {
                throw new \InvalidArgumentException('Output path must be string');
            }
            file_put_contents($outputArg, $png);
            $output->writeln('成功写入文件');
        }
    }
}
