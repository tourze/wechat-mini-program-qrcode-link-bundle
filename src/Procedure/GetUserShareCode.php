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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\FileNameGenerator\RandomNameGenerator;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Enum\EnvVersion;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramShareBundle\Entity\ShareCode;

#[MethodTag(name: '微信小程序')]
#[MethodDoc(summary: '前端获取分享用的小程序码')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetUserShareCode')]
class GetUserShareCode extends LockableProcedure
{
    #[MethodParam(description: 'AppID')]
    public string $appId = '';

    #[MethodParam(description: '跳转路径，不传就进入首页')]
    public ?string $link = null;

    #[MethodParam(description: '尺寸 默认200')]
    public int $size = 200;

    #[MethodParam(description: '打开版本')]
    public string $envVersion = 'release';

    #[MethodParam(description: '是否需要透明底色，为 true 时，生成透明底色的小程序码')]
    public bool $hyaline = false;

    /**
     * @var array<string, int>|string|null
     */
    #[MethodParam(description: '默认是{"r":0,"g":0,"b":0} 。auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示')]
    public array|string|null $lineColor = null;

    #[MethodParam(description: '覆盖中心的LOGO地址')]
    public ?string $logoUrl = null;

    public function __construct(
        private readonly NormalizerInterface $normalizer,
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
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $account = $this->detectAccount();
        $code = $this->createShareCode($account);
        $request = $this->createQrcodeRequest($account, $code);
        $response = $this->client->request($request);

        if (!is_string($response)) {
            throw new ApiException('Expected string response from client');
        }
        $png = $response;

        if ($this->shouldAddLogo()) {
            $png = $this->addLogoToPng($png);
        }

        $this->saveImageAndUpdateCode($code, $png);

        $normalized = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
        if (!is_array($normalized)) {
            throw new ApiException('Normalization failed to return array');
        }

        /** @var array<string, mixed> $normalized */
        return $normalized;
    }

    private function detectAccount(): Account
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $this->appId);
        if (null === $account) {
            throw new ApiException('找不到小程序');
        }

        return $account;
    }

    private function createShareCode(Account $account): ShareCode
    {
        $code = new ShareCode();
        $code->setAccount($account);
        $code->setLinkUrl($this->getLinkUrl());
        $code->setEnvVersion(EnvVersion::tryFrom($this->envVersion));
        $code->setValid(true);
        $code->setSize($this->size);
        $code->setUser($this->security->getUser());

        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $code;
    }

    private function getLinkUrl(): string
    {
        if (null === $this->link || '' === $this->link) {
            $envPage = $_ENV['WECHAT_MINI_PROGRAM_INDEX_PAGE'] ?? '/pages/index/index';

            return is_string($envPage) ? $envPage : '/pages/index/index';
        }

        return $this->link;
    }

    private function createQrcodeRequest(Account $account, ShareCode $code): CodeUnLimitRequest
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
        $request->setHyaline($this->hyaline);

        if (null !== $this->lineColor) {
            $request->setLineColor($this->lineColor);
        }

        return $request;
    }

    private function shouldAddLogo(): bool
    {
        return null !== $this->logoUrl && '' !== $this->logoUrl;
    }

    private function addLogoToPng(string $png): string
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($png);
        $innerWidth = ceil($img->width() / 2.25);

        $avatar = $this->createAvatar($manager);
        $avatar->resize((int) $innerWidth, (int) $innerWidth);

        $canvas = $this->createCircularCanvas($manager, $innerWidth);
        $avatar->save($canvas->encode(new PngEncoder())->toString(), true);

        $img->place($avatar, 'center');

        return $img->toPng()->toString();
    }

    private function createAvatar(ImageManager $manager): ImageInterface
    {
        if (str_starts_with($this->logoUrl ?? '', 'https://')) {
            return $manager->read($this->logoUrl);
        }

        if ('user-avatar' === $this->logoUrl) {
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
}
