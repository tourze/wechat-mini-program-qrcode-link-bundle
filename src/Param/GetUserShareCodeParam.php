<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Param;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * GetUserShareCode Procedure 的参数对象
 *
 * 用于获取分享用的小程序码
 */
readonly class GetUserShareCodeParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: 'AppID')]
        public string $appId = '',

        #[MethodParam(description: '跳转路径，不传就进入首页')]
        public ?string $link = null,

        #[MethodParam(description: '尺寸 默认200')]
        #[Assert\Positive]
        public int $size = 200,

        #[MethodParam(description: '打开版本')]
        public string $envVersion = 'release',

        #[MethodParam(description: '是否需要透明底色，为 true 时，生成透明底色的小程序码')]
        public bool $hyaline = false,

        /**
         * @var array<string, int>|string|null
         */
        #[MethodParam(description: '默认是{"r":0,"g":0,"b":0} 。auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示')]
        public array|string|null $lineColor = null,

        #[MethodParam(description: '覆盖中心的LOGO地址')]
        public ?string $logoUrl = null,
    ) {
    }
}
