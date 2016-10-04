<?php

namespace Cisco\Console\Exception;

class RecvTimeoutException extends ConsoleException
{
    public function __construct()
    {
        parent::__construct('Router doesn\'t answer');
    }
}
