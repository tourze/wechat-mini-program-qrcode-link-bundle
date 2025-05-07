<?php

namespace WechatMiniProgramQrcodeLinkBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;

#[AsCommand(name: 'wechat-mini-program:generate-unlimited-code', description: '生成指定路径和场景值的码')]
class GenerateUnlimitedCodeCommand extends Command
{
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
        $account = $this->accountRepository->find($input->getArgument('accountId'));
        if (!$account) {
            throw new \Exception('找不到小程序');
        }

        $basePath = ltrim((string) $input->getArgument('path'), '/'); // 兼容写错的情况
        $basePath = trim($basePath);

        $request = new CodeUnLimitRequest();
        $request->setAccount($account);
        $request->setScene($input->getArgument('scene'));
        $request->setPage($basePath);
        $request->setCheckPath(false);
        $request->setEnvVersion($input->getArgument('env'));
        $request->setWidth($input->getArgument('width'));
        $png = $this->client->request($request);

        if ($input->getArgument('output')) {
            file_put_contents($input->getArgument('output'), $png);
            $output->writeln('成功写入文件');
        }

        return Command::SUCCESS;
    }
}
