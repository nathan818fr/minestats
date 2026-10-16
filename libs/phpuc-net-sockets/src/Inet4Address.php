<?php
namespace PhpUC\Net\Socket;

/**
 * This class represents an Internet Protocol version 4 (IPv4) address.
 */
class Inet4Address extends InetAddress
{
    protected function __construct($address, $hostname)
    {
        parent::__construct($address, $hostname);
    }

    public function isAnyLocalAddress()
    {
        return ($this->address === "\x00\x00\x00\x00");
    }

    public function isLoopbackAddress()
    {
        // 127.
        return ($this->address[0] === "\x7F");
    }

    public function isLinkLocalAddress()
    {
        // 169.254.
        return ($this->address[0] === "\xA9" && $this->address[1] === "\xFE");
    }

    public function isSiteLocalAddress()
    {
        // 10.
        // 172.16.
        // 192.168.
        return (
            ($this->address[0] === "\x0A") ||
            ($this->address[0] === "\xAC" && $this->address[1] === "\x10") ||
            ($this->address[0] === "\xC0" && $this->address[1] === "\xA8")
        );
    }

    public function isMCGlobal()
    {
        // 224-238. !224.0.0.
        $b0 = unpack('C', $this->address[0])[1];

        return (
            ($b0 >= 224 && $b0 <= 238) &&
            ($b0 != 224 || $this->address[1] !== "\x00" || $this->address[2] !== "\x00")
        );
    }

    public function isMCNodeLocal()
    {
        return false;
    }

    public function isMCLinkLocal()
    {
        // 224.0.0.
        return ($this->address[0] === "\xE0" && $this->address[1] === "\x00" && $this->address[2] === "\x00");
    }

    public function isMCSiteLocal()
    {
        // 239.255.
        return ($this->address[0] === "\xEF" && $this->address[1] === "\xFF");
    }

    public function isMCOrgLocal()
    {
        // 239.192-195.
        $b1 = unpack('C', $this->address[1])[1];

        return ($this->address[0] === "\xEF" && $b1 >= 192 && $b1 <= 195);
    }
}
