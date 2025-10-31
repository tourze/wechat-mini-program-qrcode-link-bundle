<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatMiniProgramQrcodeLinkBundle\Request\CodeLimitRequest;

/**
 * @internal
 */
#[CoversClass(CodeLimitRequest::class)]
final class CodeLimitRequestTest extends RequestTestCase
{
    private CodeLimitRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new CodeLimitRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('/wxa/getwxacode', $this->request->getRequestPath());
    }

    public function testGetRequestOptionsWithBaseParameters(): void
    {
        // 设置基础参数
        $this->request->setScene('test-scene');
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
    }

    public function testGetRequestOptionsWithAllParameters(): void
    {
        // 设置所有参数
        $this->request->setScene('test-scene');
        $this->request->setPage('pages/test/index');
        $this->request->setEnvVersion('trial');
        $this->request->setCheckPath(false);
        $this->request->setWidth(500);
        $this->request->setAutoColor(true);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
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

    public function testWidthBoundaryValues(): void
    {
        // 测试最小宽度
        $this->request->setWidth(280);
        $this->assertEquals(280, $this->request->getWidth());

        // 测试最大宽度
        $this->request->setWidth(1280);
        $this->assertEquals(1280, $this->request->getWidth());

        // 测试超出边界的值仍然能设置（业务验证应该在其他层处理）
        $this->request->setWidth(100);
        $this->assertEquals(100, $this->request->getWidth());

        $this->request->setWidth(2000);
        $this->assertEquals(2000, $this->request->getWidth());
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

    public function testZeroWidthHandling(): void
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

        // 宽度为0时不应该包含在payload中
        $this->assertArrayNotHasKey('width', $json);
    }

    public function testNegativeWidthHandling(): void
    {
        // 测试负数宽度的处理
        $this->request->setScene('test');
        $this->request->setWidth(-100);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $json = $options['json'];
        $this->assertIsArray($json);

        // 负数宽度不应该包含在payload中
        $this->assertArrayNotHasKey('width', $json);
    }
}
