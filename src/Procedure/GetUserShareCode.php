<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\FileNameGenerator\RandomNameGenerator;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Enum\EnvVersion;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Param\GetUserShareCodeParam;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramShareBundle\Entity\ShareCode;

#[MethodTag(name: '微信小程序')]
#[MethodDoc(summary: '前端获取分享用的小程序码')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetUserShareCode')]
final class GetUserShareCode extends LockableProcedure
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AccountService $accountService,
        private readonly Client $client,
        private readonly RandomNameGenerator $randomNameGenerator,
        private readonly FilesystemOperator $filesystem,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @phpstan-param GetUserShareCodeParam $param
     */
    public function execute(GetUserShareCodeParam|RpcParamInterface $param): ArrayResult
    {
        $account = $this->detectAccount($param->appId);
        $code = $this->createShareCode($account, $param);
        $request = $this->createQrcodeRequest($account, $code, $param);
        $response = $this->client->request($request);

        if (!is_string($response)) {
            throw new ApiException('Expected string response from client');
        }
        $png = $response;

        if ($this->shouldAddLogo($param->logoUrl)) {
            $png = $this->addLogoToPng($png, $param->logoUrl);
        }

        $this->saveImageAndUpdateCode($code, $png);

        return $this->formatShareCodeResponse($code);
    }

    private function detectAccount(string $appId): Account
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $appId);
        if (null === $account) {
            throw new ApiException('找不到小程序');
        }

        return $account;
    }

    private function createShareCode(Account $account, GetUserShareCodeParam $param): ShareCode
    {
        $code = new ShareCode();
        $code->setAccount($account);
        $code->setLinkUrl($this->getLinkUrl($param->link));
        $code->setEnvVersion(EnvVersion::tryFrom($param->envVersion));
        $code->setValid(true);
        $code->setSize($param->size);
        $code->setUser($this->security->getUser());

        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $code;
    }

    private function getLinkUrl(?string $link): string
    {
        if (null === $link || '' === $link) {
            $envPage = $_ENV['WECHAT_MINI_PROGRAM_INDEX_PAGE'] ?? '/pages/index/index';

            return is_string($envPage) ? $envPage : '/pages/index/index';
        }

        return $link;
    }

    private function createQrcodeRequest(Account $account, ShareCode $code, GetUserShareCodeParam $param): CodeUnLimitRequest
    {
        $envPath = $_ENV['WECHAT_MINI_PROGRAM_SHARE_REDIRECT_PATH'] ?? 'pages/share/index';
        $basePathRaw = is_string($envPath) ? $envPath : 'pages/share/index';
        $basePath = trim($basePathRaw, '/');

        $request = new CodeUnLimitRequest();
        $request->setAccount($account);
        $request->setScene(strval($code->getId()));
        $request->setPage($basePath);
        $request->setCheckPath(false);
        $request->setEnvVersion(null !== $code->getEnvVersion() ? $code->getEnvVersion()->value : 'release');
        $request->setWidth($code->getSize() ?? 200);
        $request->setHyaline($param->hyaline);

        if (null !== $param->lineColor) {
            $request->setLineColor($param->lineColor);
        }

        return $request;
    }

    private function shouldAddLogo(?string $logoUrl): bool
    {
        return null !== $logoUrl && '' !== $logoUrl;
    }

    private function addLogoToPng(string $png, ?string $logoUrl): string
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($png);
        $innerWidth = ceil($img->width() / 2.25);

        $avatar = $this->createAvatar($manager, $logoUrl);
        $avatar->resize((int) $innerWidth, (int) $innerWidth);

        $canvas = $this->createCircularCanvas($manager, $innerWidth);
        $avatar->save($canvas->encode(new PngEncoder())->toString(), true);

        $img->place($avatar, 'center');

        return $img->toPng()->toString();
    }

    private function createAvatar(ImageManager $manager, ?string $logoUrl): ImageInterface
    {
        if (str_starts_with($logoUrl ?? '', 'https://')) {
            return $manager->read($logoUrl);
        }

        if ('user-avatar' === $logoUrl) {
            $user = $this->security->getUser();
            if (null === $user || !method_exists($user, 'getAvatar')) {
                throw new ApiException('用户对象不支持获取头像');
            }

            return $manager->read($user->getAvatar());
        }

        throw new ApiException('logoUrl不合法');
    }

    private function createCircularCanvas(ImageManager $manager, float $innerWidth): ImageInterface
    {
        $canvas = $manager->create((int) $innerWidth, (int) $innerWidth);
        $circleWidth = ceil($innerWidth / 2);

        $canvas->drawCircle((int) $circleWidth, (int) $circleWidth, function (CircleFactory $circle) use ($innerWidth): void {
            $circle->radius((int) $innerWidth);
            $circle->background('#000000');
        });

        return $canvas;
    }

    private function saveImageAndUpdateCode(ShareCode $code, string $png): void
    {
        $key = $this->randomNameGenerator->generateDateFileName('png', 'wechat-mp-share-code');
        $this->filesystem->write($key, $png);

        $code->setImageUrl($this->filesystem->publicUrl($key));
        $this->entityManager->persist($code);
        $this->entityManager->flush();
    }

    /**
     * 格式化 ShareCode 为 API 响应数组
     * 字段清单对应 restful_read 序列化组
     */
    private function formatShareCodeResponse(ShareCode $code): ArrayResult
    {
        return new ArrayResult([
            'imageUrl' => $code->getImageUrl(),
        ]);
    }
}
