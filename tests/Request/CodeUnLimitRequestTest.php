<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Color\Exceptions\InvalidColorValue;
use Spatie\Color\Rgb;
use WechatMiniProgramQrcodeLinkBundle\Exception\InvalidColorException;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;

/**
 * @internal
 */
#[CoversClass(CodeUnLimitRequest::class)]
final class CodeUnLimitRequestTest extends RequestTestCase
{
    private CodeUnLimitRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new CodeUnLimitRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('/wxa/getwxacodeunlimit', $this->request->getRequestPath());
    }

    public function testGetRequestOptionsWithBaseParameters(): void
    {
        // 设置基础参数
        $this->request->setScene('test-scene');
        $this->request->setHyaline(true);
        // 初始化必要的属性，避免未初始化错误
        $this->request->setPage('pages/index/index');
        $this->request->setEnvVersion('release');
        $this->request->setCheckPath(true);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('scene', $options['json']);
        $this->assertEquals('test-scene', $options['json']['scene']);
        $this->assertArrayHasKey('is_hyaline', $options['json']);
        $this->assertTrue($options['json']['is_hyaline']);
    }

    public function testGetRequestOptionsWithAllParameters(): void
    {
        // 设置所有参数
        $this->request->setScene('test-scene');
        $this->request->setHyaline(true);
        $this->request->setPage('pages/test/index');
        $this->request->setEnvVersion('trial');
        $this->request->setCheckPath(false);
        $this->request->setWidth(500);
        $this->request->setLineColor(['r' => 255, 'g' => 0, 'b' => 0]);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('scene', $options['json']);
        $this->assertArrayHasKey('is_hyaline', $options['json']);
        $this->assertArrayHasKey('page', $options['json']);
        $this->assertArrayHasKey('env_version', $options['json']);
        $this->assertArrayHasKey('check_path', $options['json']);
        $this->assertArrayHasKey('width', $options['json']);
        $this->assertArrayHasKey('line_color', $options['json']);

        $this->assertEquals('test-scene', $options['json']['scene']);
        $this->assertTrue($options['json']['is_hyaline']);
        $this->assertEquals('pages/test/index', $options['json']['page']);
        $this->assertEquals('trial', $options['json']['env_version']);
        $this->assertFalse($options['json']['check_path']);
        $this->assertEquals(500, $options['json']['width']);
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], $options['json']['line_color']);
    }

    public function testGettersAndSetters(): void
    {
        // 测试所有的 getter 和 setter
        $this->request->setScene('test-scene');
        $this->assertEquals('test-scene', $this->request->getScene());

        $this->request->setPage('pages/test/index');
        $this->assertEquals('pages/test/index', $this->request->getPage());

        $this->request->setCheckPath(false);
        $this->assertFalse($this->request->isCheckPath());

        $this->request->setEnvVersion('trial');
        $this->assertEquals('trial', $this->request->getEnvVersion());

        $this->request->setWidth(500);
        $this->assertEquals(500, $this->request->getWidth());

        $this->request->setAutoColor(true);
        $this->assertTrue($this->request->isAutoColor());

        $this->request->setHyaline(true);
        $this->assertTrue($this->request->isHyaline());

        // 测试颜色设置和获取
        $lineColorArray = ['r' => 255, 'g' => 0, 'b' => 0];
        $this->request->setLineColor($lineColorArray);
        $this->assertEquals($lineColorArray, $this->request->getLineColor());

        // 测试字符串颜色设置
        $lineColorString = 'rgb(0, 255, 0)';
        $this->request->setLineColor($lineColorString);
        $this->assertEquals($lineColorString, $this->request->getLineColor());
    }

    public function testGetLineColorRgbWithArrayColor(): void
    {
        $lineColorArray = ['r' => 255, 'g' => 0, 'b' => 0];
        $this->request->setLineColor($lineColorArray);

        $rgb = $this->request->getLineColorRgb();
        $this->assertInstanceOf(Rgb::class, $rgb);
        $this->assertEquals(255, $rgb->red());
        $this->assertEquals(0, $rgb->green());
        $this->assertEquals(0, $rgb->blue());
    }

    public function testGetLineColorRgbWithStringColor(): void
    {
        $lineColorString = 'rgb(0, 255, 0)';
        $this->request->setLineColor($lineColorString);

        $rgb = $this->request->getLineColorRgb();
        $this->assertInstanceOf(Rgb::class, $rgb);
        $this->assertEquals(0, $rgb->red());
        $this->assertEquals(255, $rgb->green());
        $this->assertEquals(0, $rgb->blue());
    }

    public function testGetLineColorRgbWithInvalidColor(): void
    {
        $this->request->setLineColor(null);

        $this->expectException(InvalidColorException::class);
        $this->expectExceptionMessage('找不到合适的颜色');

        $this->request->getLineColorRgb();
    }

    public function testSceneBoundaryLength(): void
    {
        // 测试空场景
        $this->request->setScene('');
        $this->assertEquals('', $this->request->getScene());

        // 测试32个字符（边界值）
        $scene32chars = str_repeat('a', 32);
        $this->request->setScene($scene32chars);
        $this->assertEquals($scene32chars, $this->request->getScene());

        // 测试超过32个字符（应该仍然能设置，具体限制由微信API处理）
        $scene33chars = str_repeat('a', 33);
        $this->request->setScene($scene33chars);
        $this->assertEquals($scene33chars, $this->request->getScene());
    }

    public function testWidthBoundaryValues(): void
    {
        // 测试最小宽度
        $this->request->setWidth(280);
        $this->assertEquals(280, $this->request->getWidth());

        // 测试最大宽度
        $this->request->setWidth(1280);
        $this->assertEquals(1280, $this->request->getWidth());

        // 测试超出边界的值仍然能设置
        $this->request->setWidth(100);
        $this->assertEquals(100, $this->request->getWidth());

        $this->request->setWidth(2000);
        $this->assertEquals(2000, $this->request->getWidth());
    }

    public function testEmptyAndNullValues(): void
    {
        // 测试空字符串页面处理
        $this->request->setScene('test');
        $this->request->setPage('');
        $this->request->setEnvVersion('');

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $json = $options['json'];
        $this->assertIsArray($json);

        // 空字符串应该不包含在payload中
        $this->assertArrayNotHasKey('page', $json);
        $this->assertArrayNotHasKey('env_version', $json);
        $this->assertArrayHasKey('scene', $json);
    }

    public function testZeroAndNegativeWidthHandling(): void
    {
        // 测试宽度为0的处理
        $this->request->setScene('test');
        $this->request->setWidth(0);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $json = $options['json'];
        $this->assertIsArray($json);
        $this->assertArrayNotHasKey('width', $json);

        // 测试负数宽度的处理
        $this->request->setWidth(-100);
        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $json = $options['json'];
        $this->assertIsArray($json);
        $this->assertArrayNotHasKey('width', $json);
    }

    public function testColorBoundaryValues(): void
    {
        // 测试RGB边界值
        $maxRgb = ['r' => 255, 'g' => 255, 'b' => 255];
        $this->request->setLineColor($maxRgb);
        $this->assertEquals($maxRgb, $this->request->getLineColor());

        $minRgb = ['r' => 0, 'g' => 0, 'b' => 0];
        $this->request->setLineColor($minRgb);
        $this->assertEquals($minRgb, $this->request->getLineColor());

        // 测试超出边界的RGB值（应该仍然能设置，但RGB解析时会处理）
        $invalidRgb = ['r' => 300, 'g' => -50, 'b' => 150];
        $this->request->setLineColor($invalidRgb);
        $this->assertEquals($invalidRgb, $this->request->getLineColor());
    }

    public function testInvalidColorFormats(): void
    {
        // 测试无效的颜色格式字符串 - 期望底层的异常类型
        $invalidColorString = 'invalid-color-format';
        $this->request->setLineColor($invalidColorString);

        $this->expectException(InvalidColorValue::class);

        $this->request->getLineColorRgb();
    }

    public function testHyalineDefaultValue(): void
    {
        // 测试透明度默认值（应该有一个默认值）
        $this->request->setScene('test');
        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $json = $options['json'];
        $this->assertIsArray($json);

        // hyaline字段应该有默认值
        $this->assertArrayHasKey('is_hyaline', $json);
        $this->assertIsBool($json['is_hyaline']);
    }
}
