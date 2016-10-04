<?php

namespace Cisco;

use Cisco\AccessList\AccessList;
use Cisco\Connection\ConnectionInterface;
use Cisco\Console\Console;

class CiscoManager
{
    protected $connection;
    protected $console;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        $this->connection->connect();
        $stream = $this->connection->getStream();

        $this->console = new Console(
            $stream,
            $this->connection->getEnablePassword()
        );
    }

    public function getAccessList($name, $type)
    {
        return new AccessList($this->console, $name, $type);
    }

    public function getConsole()
    {
        return $this->console;
    }
}
