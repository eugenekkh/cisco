<?php

namespace Cisco\Console\Exception;

class HostnameException extends ConsoleException
{
    public function __construct()
    {
        parent::__construct('Hostname cannot be detected');
    }
}
