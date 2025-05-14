<?php

namespace WechatMiniProgramQrcodeLinkBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\ImageManager;
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
use WechatMiniProgramBundle\Enum\EnvVersion;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramShareBundle\Entity\ShareCode;

#[MethodTag('微信小程序')]
#[MethodDoc('前端获取分享码')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('GetUserShareCode')]
class GetUserShareCode extends LockableProcedure
{
    #[MethodParam('AppID')]
    public string $appId = '';

    #[MethodParam('跳转路径，不传就进入首页')]
    public ?string $link = null;

    #[MethodParam('尺寸 默认200')]
    public int $size = 200;

    #[MethodParam('打开版本')]
    public string $envVersion = 'release';

    #[MethodParam('是否需要透明底色，为 true 时，生成透明底色的小程序码')]
    public bool $hyaline = false;

    #[MethodParam('默认是{"r":0,"g":0,"b":0} 。auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示')]
    public array|string|null $lineColor = null;

    #[MethodParam('覆盖中心的LOGO地址')]
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

    public function execute(): array
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $this->appId);
        if (!$account) {
            throw new ApiException('找不到小程序');
        }

        $code = new ShareCode();
        $code->setAccount($account);

        $linkUrl = $this->link;
        if (!$linkUrl) {
            $linkUrl = $_ENV['WECHAT_MINI_PROGRAM_INDEX_PAGE'] ?? '/pages/index/index';
        }

        $code->setLinkUrl($linkUrl);

        $code->setEnvVersion(EnvVersion::tryFrom($this->envVersion));
        $code->setValid(true);
        $code->setSize($this->size);
        $code->setUser($this->security->getUser());
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        // 先保存，生成码后再update一次
        // 中转页路径可配置化
        $basePath = $_ENV['WECHAT_MINI_PROGRAM_SHARE_REDIRECT_PATH'] ?? 'pages/share/index';
        $basePath = trim((string) $basePath, '/'); // 兼容写错的情况
        $request = new CodeUnLimitRequest();
        $request->setAccount($account);
        $request->setScene(strval($code->getId()));
        $request->setPage($basePath);
        $request->setCheckPath(false);
        $request->setEnvVersion($code->getEnvVersion() ? $code->getEnvVersion()->value : 'release');
        $request->setWidth($code->getSize());
        $request->setHyaline($this->hyaline);

        // 颜色配置
        if (null !== $this->lineColor) {
            $request->setLineColor($this->lineColor);
        }

        $png = $this->client->request($request);

        if ($this->logoUrl) {
            // 参考 https://www.imnobby.com/2022/06/02/php-crop-image-from-square-to-circle-aka-set-image-border-radius/

            $manager = new ImageManager(new Driver());
            $img = $manager->read($png);
            $innerWidth = ceil($img->width() / 2.25);

            if (str_starts_with($this->logoUrl, 'https://')) {
                $avatar = $manager->read($this->logoUrl);
            } elseif ('user-avatar' === $this->logoUrl) {
                $avatar = $manager->read($this->security->getUser()->getAvatar());
            } else {
                throw new ApiException('logoUrl不合法');
            }
            $avatar->resize($innerWidth, $innerWidth);
            // create empty canvas with transparent background
            $canvas = $manager->create($innerWidth, $innerWidth);
            // draw a black circle on it
            $circleWidth = ceil($innerWidth / 2);
            $canvas->drawCircle($circleWidth, $circleWidth, function (CircleFactory $circle) use ($innerWidth) {
                $circle->radius($innerWidth); // radius of circle in pixels
                $circle->background('#000000'); // background color
            });
            $avatar->save($canvas->encode(new PngEncoder(), 75), true); // 75 is the image compression ratio

            $img->place($avatar, 'center');
            $png = $img->toPng();
        }

        $key = $this->randomNameGenerator->generateDateFileName('png', 'wechat-mp-share-code');
        $this->filesystem->write($key, $png);

        $code->setImageUrl($this->filesystem->publicUrl($key));
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
    }
}
