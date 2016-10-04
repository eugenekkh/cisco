<?php

namespace Cisco\AccessList\Entry;

use IPTools\Network;

class ExtendedEntry implements EntryInterface
{
    const ACTION_PERMIT = 'permit';
    const ACTION_DENY = 'deny';

    const PROTOCOL_IP = 'ip';
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';

    protected $action = self::ACTION_PERMIT;
    protected $protocol = self::PROTOCOL_IP;

    protected $source = '0.0.0.0/0';
    protected $destination = '0.0.0.0/0';

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $source = Network::parse($this->source);
        $destination = Network::parse($this->destination);

        return sprintf(
            '%s %s %s %s %s %s',
            $this->action,
            $this->protocol,
            $source->getNetwork(),
            $source->getWildcard(),
            $destination->getNetwork(),
            $destination->getWildcard()
        );
    }
}
