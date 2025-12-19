<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Param;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use WechatMiniProgramQrcodeLinkBundle\Param\GetUserShareCodeParam;

/**
 * GetUserShareCodeParam 单元测试
 *
 * @internal
 */
#[CoversClass(GetUserShareCodeParam::class)]
final class GetUserShareCodeParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new GetUserShareCodeParam();

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $param = new GetUserShareCodeParam();

        $this->assertSame('', $param->appId);
        $this->assertNull($param->link);
        $this->assertSame(200, $param->size);
        $this->assertSame('release', $param->envVersion);
        $this->assertFalse($param->hyaline);
        $this->assertNull($param->lineColor);
        $this->assertNull($param->logoUrl);
    }

    public function testConstructorWithAllParameters(): void
    {
        $lineColor = ['r' => 255, 'g' => 0, 'b' => 0];
        $param = new GetUserShareCodeParam(
            appId: 'wx123456',
            link: '/pages/detail/index',
            size: 300,
            envVersion: 'trial',
            hyaline: true,
            lineColor: $lineColor,
            logoUrl: 'https://example.com/logo.png',
        );

        $this->assertSame('wx123456', $param->appId);
        $this->assertSame('/pages/detail/index', $param->link);
        $this->assertSame(300, $param->size);
        $this->assertSame('trial', $param->envVersion);
        $this->assertTrue($param->hyaline);
        $this->assertSame($lineColor, $param->lineColor);
        $this->assertSame('https://example.com/logo.png', $param->logoUrl);
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(GetUserShareCodeParam::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPropertiesArePublicReadonly(): void
    {
        $reflection = new \ReflectionClass(GetUserShareCodeParam::class);

        $properties = ['appId', 'link', 'size', 'envVersion', 'hyaline', 'lineColor', 'logoUrl'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "{$propertyName} should be public");
            $this->assertTrue($property->isReadOnly(), "{$propertyName} should be readonly");
        }
    }

    public function testValidationFailsWhenSizeIsNotPositive(): void
    {
        $param = new GetUserShareCodeParam(size: -10);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $violations = $validator->validate($param);

        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidationPassesWithValidParameters(): void
    {
        $param = new GetUserShareCodeParam(size: 250);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $violations = $validator->validate($param);

        $this->assertCount(0, $violations);
    }

    public function testHasMethodParamAttributes(): void
    {
        $reflection = new \ReflectionClass(GetUserShareCodeParam::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $attrs = $parameter->getAttributes(\Tourze\JsonRPC\Core\Attribute\MethodParam::class);
            $this->assertNotEmpty($attrs, "Parameter {$parameter->getName()} should have MethodParam attribute");
        }
    }
}
