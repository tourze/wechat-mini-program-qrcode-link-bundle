<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramQrcodeLinkBundle\Exception\AccountNotFoundException;

/**
 * @internal
 */
#[CoversClass(AccountNotFoundException::class)]
final class AccountNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return AccountNotFoundException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return \RuntimeException::class;
    }
}
