<?php

namespace Cisco\Connection;

abstract class AbstractConnection implements ConnectionInterface
{
    protected $host;
    protected $port;
    protected $enablePassword;

    public function __construct($host, $port, $enablePassword = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->enablePassword = $enablePassword;
    }

    public function getEnablePassword()
    {
        return $this->enablePassword;
    }
}
