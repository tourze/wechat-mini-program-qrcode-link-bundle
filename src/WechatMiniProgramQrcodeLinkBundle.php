<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle;
use Tourze\JsonRPCLockBundle\JsonRPCLockBundle;
use Tourze\JsonRPCSecurityBundle\JsonRPCSecurityBundle;
use WechatMiniProgramBundle\WechatMiniProgramBundle;
use WechatMiniProgramShareBundle\WechatMiniProgramShareBundle;

class WechatMiniProgramQrcodeLinkBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            WechatMiniProgramBundle::class => ['all' => true],
            WechatMiniProgramShareBundle::class => ['all' => true],
            JsonRPCEndpointBundle::class => ['all' => true],
            JsonRPCLockBundle::class => ['all' => true],
            JsonRPCSecurityBundle::class => ['all' => true],
        ];
    }
}
