<?php

use Cisco\Console\Console;

class CiscoConsoleTest extends PHPUnit_Framework_TestCase
{
    protected $stream;
    protected $console;

    public function setUp()
    {
        parent::setUp();

        $this->stream = fopen('php://memory','r+');
        $this->write('RouterName>');
        $this->console = new Console($this->stream, '123', 1, 1);
    }

    public function testHost()
    {
        $this->assertEquals('RouterName', $this->console->getHostname());
    }

    public function testConfigureTerminal()
    {
        $this->write("RouterName#\nconfigure terminal\nRouterName(config)#\n");

        $this->console->configureTerminal();

        $this->assertTrue($this->console->isEnable());
        $this->assertTrue($this->console->isConfigure());
    }

    /**
     * @expectedException Cisco\Console\Exception\TurnModeException
     */
    public function testEnableException()
    {
        $this->write('RouterName>');
        $this->console->enable();
    }

    public function testEnableWithoutPassword()
    {
        $this->write('RouterName#');
        $this->console->enable();

        $this->assertTrue($this->console->isEnable());
    }

    public function testEnableWithPassword()
    {
        $this->write("Password:123\nRouterName#\n");
        $this->console->enable();

        $this->assertTrue($this->console->isEnable());
    }

    /**
     * @expectedException Cisco\Console\Exception\RecvTimeoutException
     */
    public function testTimeout()
    {
        $stream = fopen('php://memory','r+');

        $console = new Console($stream, '123', 0);
    }

    public function write($content)
    {
        fflush($this->stream);
        fwrite($this->stream, $content);
        rewind($this->stream);
    }
}
