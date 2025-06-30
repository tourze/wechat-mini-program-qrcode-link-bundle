<?php

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WechatMiniProgramQrcodeLinkBundle\Exception\AccountNotFoundException;

class AccountNotFoundExceptionTest extends TestCase
{
    public function testItIsRuntimeException(): void
    {
        $exception = new AccountNotFoundException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testItCanBeCreatedWithMessage(): void
    {
        $message = 'Account not found';
        $exception = new AccountNotFoundException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testItCanBeCreatedWithMessageAndCode(): void
    {
        $message = 'Account not found';
        $code = 404;
        $exception = new AccountNotFoundException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testItCanBeCreatedWithMessageCodeAndPrevious(): void
    {
        $message = 'Account not found';
        $code = 404;
        $previous = new RuntimeException('Previous exception');
        $exception = new AccountNotFoundException($message, $code, $previous);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}