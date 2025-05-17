# WechatMiniProgramQrcodeLinkBundle 测试说明

## 测试覆盖范围

本测试套件为 WechatMiniProgramQrcodeLinkBundle 提供全面的单元测试覆盖，包括以下组件：

### Request 组件测试
- `CodeUnLimitRequestTest`: 测试无限制二维码请求类的功能
  - 请求路径正确性
  - 请求选项生成
  - 颜色处理功能
  - 所有属性的 getter/setter

- `CodeLimitRequestTest`: 测试有限制二维码请求类的功能
  - 请求路径正确性
  - 请求选项生成
  - 所有属性的 getter/setter

### Command 组件测试
- `GenerateUnlimitedCodeCommandTest`: 测试生成无限制二维码命令
  - 有效输入情况下的代码生成
  - 输出路径保存功能
  - 无效账户处理
  - 自定义环境和尺寸参数
  - 路径处理功能

### Procedure 组件测试
- `GetUserShareCodeTest`: 测试获取用户分享码的过程
  - 有效应用ID的分享码生成
  - 无效应用ID的异常处理
  - 默认链接配置
  - Logo URL处理
  - 用户头像作为Logo
  - 无效Logo URL的异常处理

## 运行测试

在项目根目录执行以下命令运行所有测试：

```bash
./vendor/bin/phpunit packages/wechat-mini-program-qrcode-link-bundle/tests
```

## 测试设计原则

1. **行为驱动测试**：测试用例聚焦于组件的行为而非实现细节
2. **边界条件覆盖**：测试覆盖正常流程、异常情况、边界条件和极端参数
3. **测试隔离**：使用模拟对象隔离外部依赖
4. **断言粒度**：精确断言关键输出、状态变更和异常抛出
5. **命名规范**：测试方法命名采用 `test功能描述_场景描述` 格式

## 注意事项

- 测试依赖于 PHPUnit 10.0 及以上版本
- 测试需要在 monorepo 根目录执行，不要在包目录内执行
- 测试不依赖于任何外部服务或数据库 