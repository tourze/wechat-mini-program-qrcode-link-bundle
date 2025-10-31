<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramQrcodeLinkBundle\Exception\InvalidColorException;

/**
 * @internal
 */
#[CoversClass(InvalidColorException::class)]
final class InvalidColorExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return InvalidColorException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return \InvalidArgumentException::class;
    }
}
