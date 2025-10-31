<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Request;

use WechatMiniProgramBundle\Request\RawResponseAPI;
use WechatMiniProgramBundle\Request\WithAccountRequest;

/**
 * 获取小程序码
 * 该接口用于获取小程序码，适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html
 */
class CodeLimitRequest extends WithAccountRequest implements RawResponseAPI
{
    /**
     * 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
     */
    private string $scene;

    /**
     * 默认是主页，页面 page，例如 pages/index/index，根路径前不要填加 /，不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面。
     */
    private ?string $page = null;

    /**
     * 默认是true，检查page 是否存在，为 true 时 page 必须是已经发布的小程序存在的页面（否则报错）；为 false 时允许小程序未发布或者 page 不存在， 但page 有数量上限（60000个）请勿滥用。
     */
    private bool $checkPath = true;

    /**
     * 要打开的小程序版本。正式版为 "release"，体验版为 "trial"，开发版为 "develop"。默认是正式版
     */
    private ?string $envVersion = null;

    /**
     * 默认430，二维码的宽度，单位 px，最小 280px，最大 1280px
     */
    private int $width = 430;

    /**
     * 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
     */
    private bool $autoColor = false;

    public function getRequestPath(): string
    {
        return '/wxa/getwxacode';
    }

    public function getRequestOptions(): ?array
    {
        $payload = [
            'scene' => $this->getScene(),
        ];
        if (null !== $this->getPage() && '' !== $this->getPage()) {
            $payload['page'] = $this->getPage();
        }
        if (null !== $this->getEnvVersion() && '' !== $this->getEnvVersion()) {
            $payload['env_version'] = $this->getEnvVersion();
        }

        $payload['check_path'] = $this->isCheckPath();
        $payload['auto_color'] = $this->isAutoColor();
        if ($this->getWidth() > 0) {
            $payload['width'] = $this->getWidth();
        }

        return [
            'json' => $payload,
        ];
    }

    public function getScene(): string
    {
        return $this->scene;
    }

    public function setScene(string $scene): void
    {
        $this->scene = $scene;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(string $page): void
    {
        $this->page = $page;
    }

    public function isCheckPath(): bool
    {
        return $this->checkPath;
    }

    public function setCheckPath(bool $checkPath): void
    {
        $this->checkPath = $checkPath;
    }

    public function getEnvVersion(): ?string
    {
        return $this->envVersion;
    }

    public function setEnvVersion(string $envVersion): void
    {
        $this->envVersion = $envVersion;
    }

    public function isAutoColor(): bool
    {
        return $this->autoColor;
    }

    public function setAutoColor(bool $autoColor): void
    {
        $this->autoColor = $autoColor;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }
}
