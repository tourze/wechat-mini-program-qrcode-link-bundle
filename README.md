# WeChat Mini Program QRCode Link Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-mini-program-qrcode-link-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-qrcode-link-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&label=Build&style=flat-square)](https://github.com/tourze/php-monorepo/actions/workflows/ci.yml)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A powerful Symfony bundle for generating WeChat Mini Program QR codes and share links 
with advanced features including custom logos, colors, transparent backgrounds, 
and comprehensive JSON-RPC API integration.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Security](#security)
- [Quick Start](#quick-start)
  - [Basic Usage](#basic-usage)
  - [JSON-RPC API](#json-rpc-api)
  - [Console Command](#console-command)
- [Command Parameters](#command-parameters)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Services Configuration](#services-configuration)
- [Advanced Usage](#advanced-usage)
  - [Custom Logo Overlay](#custom-logo-overlay)
  - [Color Customization](#color-customization)
  - [Transparent Background](#transparent-background)
  - [Batch Processing](#batch-processing)
- [API Reference](#api-reference)
  - [CodeUnLimitRequest](#codeunlimitrequest)
  - [GetUserShareCode](#getusersharecode)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🔗 Generate unlimited WeChat Mini Program QR codes
- 🎨 Custom colors and transparent backgrounds
- 🖼️ Logo overlay support (including user avatars)
- 🚀 JSON-RPC API integration
- 💾 Share code management with database persistence
- 🔒 User authentication and security
- 🛠️ Console command for batch generation
- 📊 Multiple environment support (release, trial, develop)

## Installation

```bash
composer require tourze/wechat-mini-program-qrcode-link-bundle
```

## Dependencies

This bundle requires:

- PHP 8.1 or higher
- Symfony 6.4 or higher  
- WeChat Mini Program Bundle
- Doctrine ORM
- Flysystem
- Intervention Image v3

## Security

This bundle implements several security measures:

- User authentication required for JSON-RPC procedures
- Input validation for all parameters
- Safe image processing with size limits
- URL validation for logo sources

## Quick Start

### Basic Usage

```php
<?php

use WechatMiniProgramQrcodeLinkBundle\Request\CodeUnLimitRequest;
use WechatMiniProgramBundle\Service\Client;

// Create unlimited QR code request
$request = new CodeUnLimitRequest();
$request->setAccount($account);
$request->setScene('user-123');
$request->setPage('pages/product/detail');
$request->setCheckPath(false);
$request->setEnvVersion('release');
$request->setWidth(750);

// Generate QR code
$png = $client->request($request);
file_put_contents('qrcode.png', $png);
```

### JSON-RPC API

```javascript
// Get user share code with custom logo
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

### Console Command

Generate QR codes via command line:

```bash
# Generate unlimited QR code
php bin/console wechat-mini-program:generate-unlimited-code \
  --account-id=1 \
  --path="pages/product/detail" \
  --scene="product-123" \
  --env="release" \
  --width=750 \
  --output="qrcode.png"
```

## Command Parameters

- `accountId` (required): WeChat Mini Program account ID
- `path` (required): Target page path (e.g., "pages/index/index")
- `scene` (required): Scene value (max 32 characters)
- `env` (optional): Environment version (release|trial|develop, default: release)
- `width` (optional): QR code width in pixels (default: 750)
- `output` (optional): Output file path

## Configuration

### Environment Variables

```bash
# Default index page
WECHAT_MINI_PROGRAM_INDEX_PAGE=/pages/index/index

# Share redirect path
WECHAT_MINI_PROGRAM_SHARE_REDIRECT_PATH=pages/share/index
```

### Services Configuration

Register in your `services.yaml`:

```yaml
services:
  WechatMiniProgramQrcodeLinkBundle\Command\GenerateUnlimitedCodeCommand:
    arguments:
      $accountRepository: '@WechatMiniProgramBundle\Repository\AccountRepository'
      $client: '@WechatMiniProgramBundle\Service\Client'
    tags:
      - { name: console.command }
```

## Advanced Usage

### Custom Logo Overlay

```php
// Using direct URL
$procedure = new GetUserShareCode();
$procedure->logoUrl = 'https://example.com/logo.png';

// Using user avatar
$procedure->logoUrl = 'user-avatar';
```

### Color Customization

```php
// RGB color array
$request->setLineColor(['r' => 255, 'g' => 0, 'b' => 0]);

// Color string
$request->setLineColor('#FF0000');
```

### Transparent Background

```php
$request->setHyaline(true);
```

### Batch Processing

```php
// Process multiple QR codes
$codes = [];
foreach ($products as $product) {
    $request = new CodeUnLimitRequest();
    $request->setScene("product-{$product->getId()}");
    $codes[] = $client->request($request);
}
```

## API Reference

### CodeUnLimitRequest

Main request class for generating unlimited QR codes.

## Properties

- `scene`: Scene value (max 32 characters)
- `page`: Target page path
- `checkPath`: Validate page existence
- `envVersion`: Environment (release|trial|develop)
- `width`: QR code width (280-1280px)
- `autoColor`: Auto color configuration
- `hyaline`: Transparent background
- `lineColor`: Custom line color

### GetUserShareCode

JSON-RPC procedure for generating user share codes with logo support.

## Parameters

- `appId`: WeChat Mini Program App ID
- `link`: Target page link
- `size`: QR code size (default: 200)
- `envVersion`: Environment version
- `hyaline`: Transparent background
- `lineColor`: Custom line color
- `logoUrl`: Logo overlay URL

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.