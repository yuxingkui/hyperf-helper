<?php

declare(strict_types=1);

namespace Yuxk\Helper\Contracts;

interface NotifyInterface
{
    public function sendMsg(string $message, string $to);
}
