<?php

namespace Cisco\Console\Exception;

class CommandException extends ConsoleException
{
    protected $command;

    public function __construct($message, $command)
    {
        parent::__construct($message);

        $this->command = $command;
    }
}
