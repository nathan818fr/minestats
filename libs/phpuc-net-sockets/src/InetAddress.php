<?php
namespace PhpUC\Net\Socket;

use PhpUC\IO\Stream\IOException;

/**
 * This class represents an Internet Protocol (IP) address.
 */
abstract class InetAddress
{
    const IPV4 = 1 << 0;
    const IPV6 = 1 << 1;

    /**
     * Returns an InetAddress object given the raw IP address .
     *
     * @param string      $address  the raw IP address as binary string
     * @param null|string $hostname the hostname
     *
     * @return InetAddress an InetAddress object created from the raw IP address
     * @throws UnknownHostException if IP address is of illegal length
     */
    public static function getByAddress($address, $hostname = null)
    {
        $addrLen = strlen($address);
        if ($addrLen === 4) {
            return new Inet4Address($address, $hostname);
        } elseif ($addrLen == 16) {
            return new Inet6Address($address, $hostname);
        }

        throw new UnknownHostException($address);
    }

    /**
     * @param $host
     *
     * @return InetAddress
     * @throws UnknownHostException if no IP address for the host could be found
     */
    public static function getByName($host, $fetch = null)
    {
        return (self::getAllByName($host, $fetch))[0];
    }

    /**
     * @param $host
     *
     * @return InetAddress[]
     * @throws UnknownHostException if no IP address for the host could be found
     */
    public static function getAllByName($host, $fetch = null)
    {
        if ($fetch === null) {
            $fetch = self::IPV4;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return [self::getByAddress(inet_pton($host))];
        } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return [self::getByAddress(inet_pton($host))];
        }

        $addrs = [];
        if ($fetch & self::IPV4) {
            $v4Addrs = @gethostbynamel($host);
            if ($v4Addrs !== false) {
                $v4Addrs = array_unique($v4Addrs); // gethostbynamel seems to returns duplicate values sometime
                foreach ($v4Addrs as $v4Addr) {
                    $addrs[] = self::getByAddress(inet_pton($v4Addr), $host);
                }
            }
        }
        if ($fetch & self::IPV6) {
            $v6Addrs = dns_get_record($host, DNS_AAAA);
            if ($v6Addrs !== false) {
                foreach ($v6Addrs as $v6Addr) {
                    $addrs[] = self::getByAddress(inet_pton($v6Addr['ipv6']), $host);
                }
            }
        }

        if (empty($addrs)) {
            throw new UnknownHostException($host);
        }

        return $addrs;
    }

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $hostAddress;

    /**
     * @param string $address
     * @param string $hostname
     */
    protected function __construct($address, $hostname = null)
    {
        $this->address = $address;
        $this->hostAddress = inet_ntop($address);
        $this->hostname = $hostname;
    }

    /**
     * Returns the raw IP address as binary string.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the IP address string in textual presentation.
     *
     * @return string
     */
    public function getHostAddress()
    {
        return $this->hostAddress;
    }

    /**
     * Gets the host name for this IP address.
     *
     * @return string
     */
    public function getHostname()
    {
        if ($this->hostname === null) {
            $hostname = @gethostbyaddr($this->hostAddress);
            if ($hostname === false) {
                $hostname = $this->hostAddress;
            }
            $this->hostname = $hostname;
        }

        return $this->hostname;
    }

    /**
     * Utility routine to check if the InetAddress in a wildcard address.
     *
     * @return bool a boolean indicating if the Inetaddress is a wildcard address.
     */
    public abstract function isAnyLocalAddress();

    /**
     * Utility routine to check if the InetAddress is a loopback address.
     *
     * @return bool a boolean indicating if the InetAddress is a loopback address; or false otherwise.
     */
    public abstract function isLoopbackAddress();

    /**
     * Utility routine to check if the InetAddress is an link local address.
     *
     * @return bool a boolean indicating if the InetAddress is a link local address; or false if address is not a link
     * local unicast address.
     */
    public abstract function isLinkLocalAddress();

    /**
     * Utility routine to check if the InetAddress is a site local address.
     *
     * @return bool a boolean indicating if the InetAddress is a site local address; or false if address is not a site
     * local unicast address.
     */
    public abstract function isSiteLocalAddress();

    /**
     * Utility routine to check if the multicast address has global scope.
     *
     * @return bool a boolean indicating if the address has is a multicast address of global scope, false if it is not
     * of global scope or it is not a multicast address
     */
    public abstract function isMCGlobal();

    /**
     * Utility routine to check if the multicast address has node scope.
     *
     * @return bool a boolean indicating if the address has is a multicast address of node-local scope, false if it is
     * not of node-local scope or it is not a multicast address
     */
    public abstract function isMCNodeLocal();

    /**
     * Utility routine to check if the multicast address has link scope.
     *
     * @return bool a boolean indicating if the address has is a multicast address of link-local scope, false if it is
     * not of link-local scope or it is not a multicast address
     */
    public abstract function isMCLinkLocal();

    /**
     * Utility routine to check if the multicast address has site scope.
     *
     * @return bool a boolean indicating if the address has is a multicast address of site-local scope, false if it is
     * not of site-local scope or it is not a multicast address
     */
    public abstract function isMCSiteLocal();

    /**
     * Utility routine to check if the multicast address has organization scope.
     *
     * @return bool a boolean indicating if the address has is a multicast address of organization-local scope, false if
     * it is not of organization-local scope or it is not a multicast address
     */
    public abstract function isMCOrgLocal();

    /**
     * Test whether that address is reachable.
     *
     * The timeout value, in milliseconds, indicates the maximum amount of time the try should take. If the operation
     * times out before getting an answer, the host is deemed unreachable.
     *
     * @param int $timeout the time, in milliseconds, before the call aborts
     *
     * @return bool a boolean indicating if the address is reachable.
     * @throws IOException
     */
    public function isReachable($timeout)
    {
        // TODO(nathan818): Wait for Socket class
        return false;
    }
}