<?php

namespace Cisco\Console;

use Cisco\Console\Exception\ErrorMessageException;
use Cisco\Console\Exception\HostnameException;
use Cisco\Console\Exception\RecvTimeoutException;
use Cisco\Console\Exception\TurnModeException;

/**
 * Wrapper stream of the cisco console
 * The class allow send command to router and receive result without headache about blocking stream and etc
 * Just call exec
 */
class Console implements ConsoleInterface
{
    const DEFAULT_READ_LENGHT = 1024;
    const DEFAULT_TIMEOUT = 5;
    const MORE_KEYWORD = '--More--';

    /**
     * Current the enable mode state
     */
    protected $enable = false;

    protected $enablePassword;

    /**
     * Current the configuration mode state
     */
    protected $configureTerminal = false;

    protected $hostname;
    protected $stream;

    protected $readLenght = 1024;
    protected $timeout;

    /**
     * @param resource $stream
     * @param string $enablePassword
     */
    public function __construct(
        $stream,
        $enablePassword = '',
        $timeout = self::DEFAULT_TIMEOUT,
        $readLenght = self::DEFAULT_READ_LENGHT
    ) {
        $this->stream = $stream;
        $this->enablePassword = $enablePassword;
        $this->readLenght = $readLenght;
        $this->timeout = $timeout;

        $this->detectHostname();
    }

    /**
     * Enter configuration mode
     */
    public function configureTerminal()
    {
        if ($this->configureTerminal) {
            return;
        }

        $this->enable();

        fwrite($this->stream, 'configure terminal' . PHP_EOL);

        $keywords = $this->getKeywords();
        $result = $this->recv($keywords, $this->timeout);

        if (!$this->configureTerminal) {
            throw new TurnModeException('Attempt transitions to configuration mode has be failed');
        }
    }

    /**
     * Exit from configure mode
     */
    public function end()
    {
        if (!$this->configureTerminal) {
            return;
        }

        $this->exec('end');
    }

    /**
     * Turn on privileged commands
     */
    public function enable()
    {
        if ($this->enable) {
            return;
        }

        fwrite($this->stream, 'enable' . PHP_EOL);

        $keywords = $this->getKeywords();
        $keywords[] = 'Password:';

        $result = $this->recv($keywords, $this->timeout);

        if (preg_match('/Password:/', $result)) {
            fwrite($this->stream, $this->enablePassword . PHP_EOL);
            $result = $this->recv($keywords, $this->timeout);
        }

        if (!$this->enable) {
            throw new TurnModeException('Attempt transitions to privileged mode has be failed');
        }
    }

    /**
     * Get router hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * True if the router in configure mode
     *
     * @return boolen
     */
    public function isConfigure()
    {
        return $this->configureTerminal;
    }

    /**
     * True if the router in privileged mode
     *
     * @return boolen
     */
    public function isEnable()
    {
        return $this->enable;
    }

    /**
     * Execute a command on the router
     *
     * @param string $command
     *
     * @return string
     *
     * @throws ErrorMessageException
     * @throws RecvTimeoutException
     */
    public function exec($command)
    {
        fwrite($this->stream, $command . PHP_EOL);

        $result = '';
        do {
            $buffer = $this->recv($this->getKeywords(), $this->timeout);
            if (strrpos($buffer, self::MORE_KEYWORD) !== false) {
                $buffer = str_replace(self::MORE_KEYWORD, '', $buffer);
                fwrite($this->stream, " ");
                $result .= $buffer;
                continue;
            } else {
                $result .= $buffer;
                break;
            }
        } while (true);

        // Cisco tell about error
        if (preg_match('/^\% ([^\r\n]*)/m', $result, $matches)) {
            throw new ErrorMessageException($matches[1], $command);
        }

        return $result;
    }

    /**
     * @param integer $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Detect hostname of router for detecting end of command execution result
     *
     * @throws HostnameException
     */
    protected function detectHostname()
    {
        $content = $this->recv('>', $this->timeout);
        if (preg_match("/(\w+)>/", $content, $matches)) {
            $this->hostname = $matches[1];
        } else {
            throw new HostnameException();
        }
    }

    protected function detectMode(&$result)
    {
        if (substr($result, -1) == '>') {
            $this->enable = false;
            $this->configureTerminal = false;
            return;
        }

        if (substr($result, -1) == '#') {
            $this->enable = true;
        }

        if (substr($result, -2) == ')#') {
            $this->configureTerminal = true;
        } else {
            $this->configureTerminal = false;
        }
    }

    protected function getKeywords()
    {
        return array(
            self::MORE_KEYWORD,
            $this->hostname . "([^#]*)#",
            $this->hostname . ">",
        );
    }

    /**
     * Receive data from stream while don't come keyword of end
     *
     * @param array|string $keywords
     * @param integer $timeout
     *
     * @return string
     *
     * @throws RecvTimeoutException If keyword didn't received within specified time
     */
    protected function recv($keywords, $timeout)
    {
        $buffer = '';

        if (!is_array($keywords)) {
            $keywords = array($keywords);
        }

        $time = time();
        $cnt = 1000;
        while (true) {
            $buf = fread($this->stream, $this->readLenght);

            if (strlen($buf) != 0) {
                $buffer .= $buf;
                foreach ($keywords as $keyword) {
                    if (preg_match('/'.$keyword.'/', $buffer)) {
                        $this->detectMode($buffer);
                        return $buffer;
                    }
                }
            }

            $info = stream_get_meta_data($this->stream);
            if ($info['unread_bytes'] > 0)
                continue;

            if (time() - $time > $timeout) {
                throw new RecvTimeoutException();
            }

            usleep($cnt);
            if ($cnt < 500000) {
                $cnt += 100;
            }
        }
    }
}
