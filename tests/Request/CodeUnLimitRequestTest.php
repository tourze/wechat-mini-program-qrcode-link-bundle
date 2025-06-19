<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Spatie\Color\Rgb;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;

class CodeUnLimitRequestTest extends TestCase
{
    private CodeUnLimitRequest $request;

    protected function setUp(): void
    {
        $this->request = new CodeUnLimitRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('/wxa/getwxacodeunlimit', $this->request->getRequestPath());
    }

    public function testGetRequestOptions_WithBaseParameters(): void
    {
        // 设置基础参数
        $this->request->setScene('test-scene');
        $this->request->setHyaline(true);
        // 初始化必要的属性，避免未初始化错误
        $this->request->setPage('pages/index/index');
        $this->request->setEnvVersion('release');
        $this->request->setCheckPath(true);

        $options = $this->request->getRequestOptions();
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('scene', $options['json']);
        $this->assertEquals('test-scene', $options['json']['scene']);
        $this->assertArrayHasKey('is_hyaline', $options['json']);
        $this->assertTrue($options['json']['is_hyaline']);
    }

    public function testGetRequestOptions_WithAllParameters(): void
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
        $this->assertArrayHasKey('json', $options);
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

    public function testGetLineColorRgb_WithArrayColor(): void
    {
        $lineColorArray = ['r' => 255, 'g' => 0, 'b' => 0];
        $this->request->setLineColor($lineColorArray);
        
        $rgb = $this->request->getLineColorRgb();
        $this->assertInstanceOf(Rgb::class, $rgb);
        $this->assertEquals(255, $rgb->red());
        $this->assertEquals(0, $rgb->green());
        $this->assertEquals(0, $rgb->blue());
    }

    public function testGetLineColorRgb_WithStringColor(): void
    {
        $lineColorString = 'rgb(0, 255, 0)';
        $this->request->setLineColor($lineColorString);
        
        $rgb = $this->request->getLineColorRgb();
        $this->assertInstanceOf(Rgb::class, $rgb);
        $this->assertEquals(0, $rgb->red());
        $this->assertEquals(255, $rgb->green());
        $this->assertEquals(0, $rgb->blue());
    }

    public function testGetLineColorRgb_WithInvalidColor(): void
    {
        $this->request->setLineColor(null);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('找不到合适的颜色');
        
        $this->request->getLineColorRgb();
    }
} 