<?php

namespace Cisco\Connection;

class SshPasswordConnection extends AbstractConnection
{
    protected $login;
    protected $password;

    protected $connection;
    protected $stream;

    public function __construct($host, $login, $password, $enablePassword = '', $port = 22)
    {
        parent::__construct($host, $port, $enablePassword);
        $this->login = $login;
        $this->password = $password;
    }

    public function connect()
    {
        $this->connection = ssh2_connect($this->host, $this->port);

        ssh2_auth_password($this->connection, $this->login, $this->password);
        $this->stream = ssh2_shell($this->connection);
    }

    public function getStream()
    {
        return $this->stream;
    }
}
