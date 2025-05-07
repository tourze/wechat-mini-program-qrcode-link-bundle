<?php

namespace WechatMiniProgramQrcodeLinkBundle\Procedure;

use AntdCpBundle\Builder\Action\ApiCallAction;
use AppBundle\Procedure\Base\ApiCallActionProcedure;
use Doctrine\ORM\EntityManagerInterface;
use FileSystemBundle\Service\MountManager;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCSecurityBundle\Attribute\MethodPermission;
use WechatMiniProgramBundle\Enum\EnvVersion;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramUrlLinkBundle\Entity\PromotionCode;
use WechatMiniProgramUrlLinkBundle\Repository\PromotionCodeRepository;

#[Log]
#[MethodExpose(GenerateWechatMiniProgramPromotionCode::NAME)]
#[IsGranted('ROLE_OPERATOR')]
#[MethodPermission(permission: PromotionCode::class . '::renderGenCodeAction', title: '生成小程序码')]
class GenerateWechatMiniProgramPromotionCode extends ApiCallActionProcedure
{
    public const NAME = 'GenerateWechatMiniProgramPromotionCode';

    public function __construct(
        private readonly PromotionCodeRepository $codeRepository,
        private readonly Client $client,
        private readonly MountManager $mountManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getAction(): ApiCallAction
    {
        return ApiCallAction::gen()
            ->setLabel('生成小程序码')
            ->setApiName(GenerateWechatMiniProgramPromotionCode::NAME);
    }

    public function execute(): array
    {
        $that = $this->codeRepository->findOneBy(['id' => $this->id]);
        if (!$that) {
            throw new ApiException('找不到记录');
        }

        if (!$that->getAccount()) {
            throw new ApiException('请选择小程序');
        }

        $request = new CodeUnLimitRequest();
        $request->setAccount($that->getAccount());
        $request->setCheckPath(false);
        $request->setWidth(400);

        $version = $that->getEnvVersion() ?: EnvVersion::RELEASE;
        $request->setEnvVersion($version->value);

        // 中转页路径可配置化
        $basePath = $_ENV['WECHAT_MINI_PROGRAM_PROMOTION_REDIRECT_PATH'] ?? 'pages/redirect/index';
        $basePath = trim($basePath, '/'); // 兼容写错的情况

        $request->setPage($basePath);
        $request->setScene(strval($that->getId()));

        $png = $this->client->request($request);

        $key = $this->mountManager->saveContent($png, 'png', 'wechat-mp-promotion');
        $that->setImageUrl($this->mountManager->getAccessUrl($key));
        $this->entityManager->persist($that);
        $this->entityManager->flush();

        return [
            '__message' => '生成成功',
        ];
    }
}
