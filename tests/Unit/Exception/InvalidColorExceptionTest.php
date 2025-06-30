<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramQrcodeLinkBundle\Exception\InvalidColorException;

class InvalidColorExceptionTest extends TestCase
{
    public function testItIsInvalidArgumentException(): void
    {
        $exception = new InvalidColorException();
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testItCanBeCreatedWithMessage(): void
    {
        $message = 'Invalid color format';
        $exception = new InvalidColorException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testItCanBeCreatedWithMessageAndCode(): void
    {
        $message = 'Invalid color format';
        $code = 400;
        $exception = new InvalidColorException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testItCanBeCreatedWithMessageCodeAndPrevious(): void
    {
        $message = 'Invalid color format';
        $code = 400;
        $previous = new InvalidArgumentException('Previous exception');
        $exception = new InvalidColorException($message, $code, $previous);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}