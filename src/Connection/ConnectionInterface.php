<?php

namespace Cisco\Connection;

interface ConnectionInterface
{
    /**
     *
     */
    public function connect();

    /**
     * @return resource
     */
    public function getStream();
}
