<?php

use Cisco\AccessList\Entry\ExtendedEntry;

class CiscoExtendedEntryTest extends PHPUnit_Framework_TestCase
{
    public function providerSerializeTest()
    {
        return array(
            array(
                'permit ip 1.2.3.0 0.0.0.255 0.0.0.0 255.255.255.255',
                (new ExtendedEntry())
                    ->setAction(ExtendedEntry::ACTION_PERMIT)
                    ->setProtocol(ExtendedEntry::PROTOCOL_IP)
                    ->setSource('1.2.3.4/24')
                    ->setDestination('0.0.0.0/0')
                ,
            ),
            array(
                'deny udp 192.168.222.4 0.0.0.3 10.10.10.10 0.0.0.0',
                (new ExtendedEntry())
                    ->setAction(ExtendedEntry::ACTION_DENY)
                    ->setProtocol(ExtendedEntry::PROTOCOL_UDP)
                    ->setSource('192.168.222.4/30')
                    ->setDestination('10.10.10.10')
                ,
            ),
        );
    }

    /**
     * @dataProvider providerSerializeTest
     */
    public function testSerialize($expectedCommand, ExtendedEntry $rule)
    {
        $command = $rule->serialize();
        $this->assertEquals($expectedCommand, $command);
    }
}
