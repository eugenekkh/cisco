<?php

namespace Cisco\AccessList;

use Cisco\AccessList\Entry\EntryInterface;
use Cisco\Console\ConsoleInterface;

class AccessList
{
    const STANDARD = 'standard';
    const EXTENDED = 'extended';

    protected $name;
    protected $type;

    protected $console;

    /**
     * @param ConsoleInterface $console
     * @param string $name
     * @param string $type
     */
    public function __construct(ConsoleInterface $console, $name, $type)
    {
        $this->console = $console;
        $this->name = $name;
        $this->type = $type;

        if (!in_array($this->type, [self::STANDARD, self::EXTENDED])) {
            throw new \RuntimeException('Type of access list not supported');
        }

        $this->console->enable();
    }

    /**
     * Add an entry to the access-list
     *
     * When an entry is added without a sequence number, it is automatically
     * given a sequence number that puts it at the end of the access list.
     *
     * @param EntryInterface $entry
     * @param integer $sequenceNumber
     */
    public function add(EntryInterface $entry, $sequenceNumber = null)
    {
        $this->console->configureTerminal();

        $command = $entry->serialize();

        $this->console->exec(sprintf('ip access-list %s %s', $this->type, $this->name));
        if ($sequenceNumber) {
            $this->console->exec(sprintf('%d %s', $sequenceNumber, $command));
        } else {
            $this->console->exec(sprintf('%s', $command));
        }
        $this->console->exec('exit');
    }

    /**
     * Add multiple entries to the access-list
     *
     * @param EntryInterface[] $entries
     * @param boolean $indexAsSeq
     */
    public function addMultiple($entries, $seq = false)
    {
        $this->console->configureTerminal();

        $this->console->exec(sprintf('ip access-list %s %s', $this->type, $this->name));

        foreach ($entries as $index => $entry) {
            $command = $entry->serialize();

            if ($seq) {
                $this->console->exec(sprintf('%d %s', $index, $command));
            } else {
                $this->console->exec(sprintf('%s', $command));
            }
        }

        $this->console->exec('exit');
    }

    /**
     * Remove an entry or multiple entries from the access-list by a sequence number
     *
     * @param integer|array $numbers
     */
    public function remove($numbers)
    {
        if (!is_array($numbers)) {
            $numbers = array($numbers);
        }

        $this->console->configureTerminal();

        $this->console->exec(sprintf('ip access-list %s %s', $this->type, $this->name));
        foreach ($numbers as $number) {
            $this->console->exec(sprintf('no %d', $number));
        }
        $this->console->exec('exit');
    }

    /**
     * Clear the access-list
     */
    public function clear()
    {
        $this->console->configureTerminal();

        $this->console->exec(sprintf('no ip access-list %s %s', $this->type, $this->name));
    }

    /**
     * Get hash of the access-list
     *
     * @return string
     */
    public function hash()
    {
        $show = $this->showAccessList();

        return md5($show);
    }

    /**
     * @return string
     */
    public function showAccessList()
    {
        $this->console->end();

        return $this->console->exec('show access-list ' . $this->name);
    }
}
