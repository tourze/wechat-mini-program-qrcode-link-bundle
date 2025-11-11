# 微信小程序二维码链接包

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&label=Build&style=flat-square)](https://github.com/tourze/php-monorepo/actions/workflows/ci.yml)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

功能强大的微信小程序二维码和分享链接生成 Symfony 包，支持自定义 Logo、颜色、
透明背景以及完整的 JSON-RPC API 集成。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [依赖要求](#依赖要求)
- [安全性](#安全性)
- [快速开始](#快速开始)
  - [基础用法](#基础用法)
  - [JSON-RPC API](#json-rpc-api)
  - [控制台命令](#控制台命令)
- [命令参数](#命令参数)
- [配置](#配置)
  - [环境变量](#环境变量)
  - [服务配置](#服务配置)
- [高级用法](#高级用法)
  - [自定义 Logo 叠加](#自定义-logo-叠加)
  - [颜色自定义](#颜色自定义)
  - [透明背景](#透明背景)
  - [批量处理](#批量处理)
- [API 参考](#api-参考)
  - [CodeUnLimitRequest](#codeunlimitrequest)
  - [GetUserShareCode](#getusersharecode)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 🔗 生成无限制微信小程序二维码
- 🎨 自定义颜色和透明背景
- 🖼️ Logo 叠加支持（包括用户头像）
- 🎯 JSON-RPC API 集成
- 📱 分享码管理与数据库持久化
- 🔒 用户认证和安全性
- 🎛️ 控制台命令批量生成
- 📊 多环境支持（正式版、体验版、开发版）

## 安装

```bash
composer require tourze/wechat-mini-program-qrcode-link-bundle
```

## 依赖要求

本包需要以下依赖：

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- 微信小程序包
- Doctrine ORM
- Flysystem
- Intervention Image v3

## 安全性

本包实现了多项安全措施：

- JSON-RPC 过程需要用户认证
- 所有参数都有输入验证
- 图像处理具有大小限制
- Logo 源的 URL 验证

## 快速开始

### 基础用法

```php
<?php

use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramBundle\Service\Client;

// 创建无限制二维码请求
$request = new CodeUnLimitRequest();
$request->setAccount($account);
$request->setScene('user-123');
$request->setPage('pages/product/detail');
$request->setCheckPath(false);
$request->setEnvVersion('release');
$request->setWidth(750);

// 生成二维码
$png = $client->request($request);
file_put_contents('qrcode.png', $png);
```

### JSON-RPC API

```javascript
// 获取用户分享码，带自定义 Logo
{
  "method": "GetUserShareCode",
  "params": {
    "appId": "wx1234567890abcdef",
    "link": "pages/share/index",
    "size": 200,
    "envVersion": "release",
    "hyaline": true,
    "lineColor": {"r": 255, "g": 0, "b": 0},
    "logoUrl": "https://example.com/logo.png"
  }
}
```

### 控制台命令

通过命令行生成二维码：

```bash
# 生成无限制二维码
php bin/console wechat-mini-program:generate-unlimited-code \
  --account-id=1 \
  --path="pages/product/detail" \
  --scene="product-123" \
  --env="release" \
  --width=750 \
  --output="qrcode.png"
```

## 命令参数

- `accountId` (必需): 微信小程序账号 ID
- `path` (必需): 目标页面路径 (例如 "pages/index/index")
- `scene` (必需): 场景值 (最大 32 个字符)
- `env` (可选): 环境版本 (release|trial|develop，默认: release)
- `width` (可选): 二维码宽度像素 (默认: 750)
- `output` (可选): 输出文件路径

## 配置

### 环境变量

```bash
# 默认首页
WECHAT_MINI_PROGRAM_INDEX_PAGE=/pages/index/index

# 分享跳转路径
WECHAT_MINI_PROGRAM_SHARE_REDIRECT_PATH=pages/share/index
```

### 服务配置

在你的 `services.yaml` 中注册：

```yaml
services:
  WechatMiniProgramQrcodeLinkBundle\Command\GenerateUnlimitedCodeCommand:
    arguments:
      $accountRepository: '@WechatMiniProgramBundle\Repository\AccountRepository'
      $client: '@WechatMiniProgramBundle\Service\Client'
    tags:
      - { name: console.command }
```

## 高级用法

### 自定义 Logo 叠加

```php
// 使用直接 URL
$procedure = new GetUserShareCode();
$procedure->logoUrl = 'https://example.com/logo.png';

// 使用用户头像
$procedure->logoUrl = 'user-avatar';
```

#### 小程序头像域名白名单

> ⚠️ 在小程序侧获取头像（例如先用 `wx.downloadFile` 拉取用户头像再上传到服务端）时，所有请求的域名都必须提前加入「downloadFile 合法域名」。缺少 `https://thirdwx.qlogo.cn/...` 之类的条目，就是头像偶尔下载失败的根本原因。

1. 进入 **微信公众平台 → 开发 → 开发管理 → 开发设置 → downloadFile 合法域名**，逐条添加需要访问的头像 CDN（目前不支持通配符或自动放行策略）。
2. 在前端使用头像 URL 前先统一成 HTTPS：`const safeAvatar = avatarUrl.replace(/^http:\/\//, 'https://');`，避免混用协议导致的校验失败。
3. 如果域名配额不足，可考虑先把头像转存到自己的代理/对象存储域名，再在后台统一放行该域名。

官方渠道已经确认会返回头像的域名列表如下，建议全部加入白名单：

| 域名 | 常见来源 | 官方参考 |
| --- | --- | --- |
| `https://thirdwx.qlogo.cn` | `wx.getUserProfile` 以及新版小程序用户头像 | [微信开放社区](https://developers.weixin.qq.com/community/develop/doc/000a4a8c47c658a23a9c861ba5bc00) |
| `https://wx.qlogo.cn` | 老版本 `headimgurl`、群头像等缓存 | [微信开放社区](https://developers.weixin.qq.com/community/develop/doc/000ae222a30d28683a9a86d655b000) |
| `https://mmbiz.qlogo.cn` | 公众号 / 小程序主页头像，chooseAvatar 返回样例 | [微信开放社区](https://developers.weixin.qq.com/community/develop/doc/00046c552b8fa0c49a5f6eef65e400) |
| `https://mmbiz.qpic.cn` | 公众号后台分发的方形/裁剪头像资源 | [微信开放社区](https://developers.weixin.qq.com/community/develop/doc/0006aafc930f208dba125e06866400) |

每当微信新增头像 CDN，都应第一时间同步到白名单，才能保证 `logoUrl=user-avatar` 这类功能在生成海报时稳定可用。

### 颜色自定义

```php
// RGB 颜色数组
$request->setLineColor(['r' => 255, 'g' => 0, 'b' => 0]);

// 颜色字符串
$request->setLineColor('#FF0000');
```

### 透明背景

```php
$request->setHyaline(true);
```

### 批量处理

```php
// 处理多个二维码
$codes = [];
foreach ($products as $product) {
    $request = new CodeUnLimitRequest();
    $request->setScene("product-{$product->getId()}");
    $codes[] = $client->request($request);
}
```

## API 参考

### CodeUnLimitRequest

用于生成无限制二维码的主要请求类。

## 属性

- `scene`: 场景值 (最大 32 个字符)
- `page`: 目标页面路径
- `checkPath`: 验证页面存在性
- `envVersion`: 环境 (release|trial|develop)
- `width`: 二维码宽度 (280-1280px)
- `autoColor`: 自动颜色配置
- `hyaline`: 透明背景
- `lineColor`: 自定义线条颜色

### GetUserShareCode

用于生成用户分享码的 JSON-RPC 过程，支持 Logo 叠加。

## 参数

- `appId`: 微信小程序 App ID
- `link`: 目标页面链接
- `size`: 二维码尺寸 (默认: 200)
- `envVersion`: 环境版本
- `hyaline`: 透明背景
- `lineColor`: 自定义线条颜色
- `logoUrl`: Logo 叠加 URL

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详细信息。

## 许可证

MIT 许可证。请查看 [License File](LICENSE) 获取更多信息。
