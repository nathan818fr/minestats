<?php
namespace PhpUC\Net\Socket;

/**
 * This class represents an Internet Protocol version 6 (IPv6) address.
 */
class Inet6Address extends InetAddress
{
    protected function __construct($address, $hostname)
    {
        parent::__construct($address, $hostname);
    }

    public function isAnyLocalAddress()
    {
        for ($i = 0; $i < 16; $i++) {
            if ($this->address[$i] !== "\x00") {
                return false;
            }
        }

        return true;
    }

    public function isLoopbackAddress()
    {
        for ($i = 0; $i < 15; $i++) {
            if ($this->address[$i] !== "\x00") {
                return false;
            }
        }

        return ($this->address[15] === "\x01");
    }

    private function is($b0, $b1Mask, $b1)
    {
        $b = unpack('C1/C2', $this->address);

        return ($b[1] === $b0 && ($b[2] & $b1Mask) === $b1);
    }

    public function isLinkLocalAddress()
    {
        return $this->is(254, 192, 128);
    }

    public function isSiteLocalAddress()
    {
        return $this->is(254, 192, 192);
    }

    public function isMCGlobal()
    {
        return $this->is(255, 15, 14);
    }

    public function isMCNodeLocal()
    {
        return $this->is(255, 15, 1);
    }

    public function isMCLinkLocal()
    {
        return $this->is(255, 15, 2);
    }

    public function isMCSiteLocal()
    {
        return $this->is(255, 15, 5);
    }

    public function isMCOrgLocal()
    {
        return $this->is(255, 15, 8);
    }
}