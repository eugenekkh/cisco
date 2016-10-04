<?php

namespace Cisco\Console;

interface ConsoleInterface
{
    /**
     * Execute any command on the router
     *
     * @param string $command
     *
     * @return string
     *
     * @throws ErrorMessageException
     * @throws RecvTimeoutException
     */
    public function exec($command);
}
