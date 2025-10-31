<?php

declare(strict_types=1);

namespace WechatMiniProgramQrcodeLinkBundle\Tests\Procedure;

use Symfony\Component\Security\Core\User\UserInterface;

interface AvatarUserInterface extends UserInterface
{
    public function getAvatar(): string;
}
