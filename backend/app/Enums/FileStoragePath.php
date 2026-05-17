<?php

declare(strict_types=1);

namespace App\Enums;

enum FileStoragePath: string
{
    case UserAvatar = 'users/avatars';
    case PostHeaderImage = 'posts/header_images';
}
