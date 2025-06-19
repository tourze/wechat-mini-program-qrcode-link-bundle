<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeLimitRequest;

class CodeLimitRequestTest extends TestCase
{
    private CodeLimitRequest $request;

    protected function setUp(): void
    {
        $this->request = new CodeLimitRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('/wxa/getwxacode', $this->request->getRequestPath());
    }

    public function testGetRequestOptions_WithBaseParameters(): void
    {
        // 设置基础参数
        $this->request->setScene('test-scene');
        // 初始化必要的属性，避免未初始化错误
        $this->request->setPage('pages/index/index');
        $this->request->setEnvVersion('release');
        $this->request->setCheckPath(true);

        $options = $this->request->getRequestOptions();
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('scene', $options['json']);
        $this->assertEquals('test-scene', $options['json']['scene']);
    }

    public function testGetRequestOptions_WithAllParameters(): void
    {
        // 设置所有参数
        $this->request->setScene('test-scene');
        $this->request->setPage('pages/test/index');
        $this->request->setEnvVersion('trial');
        $this->request->setCheckPath(false);
        $this->request->setWidth(500);
        $this->request->setAutoColor(true);

        $options = $this->request->getRequestOptions();
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('scene', $options['json']);
        $this->assertArrayHasKey('page', $options['json']);
        $this->assertArrayHasKey('env_version', $options['json']);
        $this->assertArrayHasKey('check_path', $options['json']);
        $this->assertArrayHasKey('width', $options['json']);
        
        $this->assertEquals('test-scene', $options['json']['scene']);
        $this->assertEquals('pages/test/index', $options['json']['page']);
        $this->assertEquals('trial', $options['json']['env_version']);
        $this->assertFalse($options['json']['check_path']);
        $this->assertEquals(500, $options['json']['width']);
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
    }

    public function testDefaultWidth(): void
    {
        // 测试默认宽度值
        $this->assertEquals(430, $this->request->getWidth());
    }

    public function testDefaultAutoColor(): void
    {
        // 测试默认自动颜色设置
        $this->assertFalse($this->request->isAutoColor());
    }
} 