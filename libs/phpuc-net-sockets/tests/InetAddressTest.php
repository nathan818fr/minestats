<?php
namespace PhpUC\Net\Socket;

use PHPUnit\Framework\TestCase;

/**
 * TODO(nathan818): Use a DNS server stub to allow offline tests? Seems boring in PHP...
 */
class InetAddressTest extends TestCase
{
    private static $ip4addr = "\xD0\x43\xDE\xDE";

    private static $ip4hostaddr = '208.67.222.222';

    private static $ip6addr = "\x26\x20\x00\x00\x0C\xCC\x00\x00\x00\x00\x00\x00\x00\x00\x00\x02";

    private static $ip6hostaddr = "2620:0:ccc::2";

    private static $isMethods = [
        'AnyLocalAddress',
        'LoopbackAddress',
        'LinkLocalAddress',
        'SiteLocalAddress',
        'MCGlobal',
        'MCNodeLocal',
        'MCLinkLocal',
        'MCSiteLocal',
        'MCOrgLocal',
    ];

    private static $addresses = [
        '0.0.0.0'         => ['AnyLocalAddress'],
        '127.0.0.1'       => ['LoopbackAddress'],
        '127.88.32.12'    => ['LoopbackAddress'],
        '169.254.0.1'     => ['LinkLocalAddress'],
        '169.254.88.124'  => ['LinkLocalAddress'],
        '10.0.0.1'        => ['SiteLocalAddress'],
        '10.45.12.9'      => ['SiteLocalAddress'],
        '172.16.0.1'      => ['SiteLocalAddress'],
        '172.16.44.245'   => ['SiteLocalAddress'],
        '192.168.0.1'     => ['SiteLocalAddress'],
        '192.168.241.3'   => ['SiteLocalAddress'],
        '224.12.0.1'      => ['MCGlobal'],
        '225.0.0.0'       => ['MCGlobal'],
        '232.100.77.36'   => ['MCGlobal'],
        '238.45.0.12'     => ['MCGlobal'],
        '224.0.0.1'       => ['MCLinkLocal'],
        '224.0.0.96'      => ['MCLinkLocal'],
        '239.255.0.1'     => ['MCSiteLocal'],
        '239.255.44.23'   => ['MCSiteLocal'],
        '239.255.3.238'   => ['MCSiteLocal'],
        '239.192.212.185' => ['MCOrgLocal'],
        '239.193.2.222'   => ['MCOrgLocal'],
        '239.195.255.0'   => ['MCOrgLocal'],
        '239.195.0.1'     => ['MCOrgLocal'],
        '87.88.89.90'     => [],
        '8.8.8.8'         => [],
        '255.255.255.255' => [],
        '68.105.47.41'    => [],
        '101.84.132.201'  => [],
        '122.173.48.94'   => [],
        '::0'             => ['AnyLocalAddress'],
        '::1'             => ['LoopbackAddress'],
        // TODO(nathan818): Add missing tests for IPv6 addr
    ];

    public function testGetByAddress()
    {
        // IPv4
        $ipv4 = InetAddress::getByAddress(self::$ip4addr);
        $this->assertInstanceOf(Inet4Address::class, $ipv4);
        $this->assertEquals(self::$ip4addr, $ipv4->getAddress());
        $this->assertEquals(self::$ip4hostaddr, $ipv4->getHostAddress());
        $this->assertEquals("resolver1.opendns.com", $ipv4->getHostname());

        $ipv4 = InetAddress::getByAddress(self::$ip4addr, 'test.fr');
        $this->assertInstanceOf(Inet4Address::class, $ipv4);
        $this->assertEquals("test.fr", $ipv4->getHostname());

        // IPv6
        $ipv6 = InetAddress::getByAddress(self::$ip6addr);
        $this->assertInstanceOf(Inet6Address::class, $ipv6);
        $this->assertEquals(self::$ip6addr, $ipv6->getAddress());
        $this->assertEquals(self::$ip6hostaddr, $ipv6->getHostAddress());
        $this->assertEquals("resolver1.ipv6-sandbox.opendns.com", $ipv6->getHostname());

        $ipv6 = InetAddress::getByAddress(self::$ip6addr, 'test.fr');
        $this->assertInstanceOf(Inet6Address::class, $ipv6);
        $this->assertEquals("test.fr", $ipv6->getHostname());

        // Bad addr
        $this->doGetByAddressExceptionTest('0');
        $this->doGetByAddressExceptionTest('127.0.0.1');
        $this->doGetByAddressExceptionTest('::1');
        $this->doGetByAddressExceptionTest('0');
    }

    private function doGetByAddressExceptionTest($address)
    {
        try {
            InetAddress::getByAddress($address);
        } catch (UnknownHostException $e) {
            return;
        }
        $this->fail('Expected UnknownHostException');
    }

    public function testGetByName()
    {
        $ip = InetAddress::getByName('127.0.0.1');
        $this->assertInstanceOf(Inet4Address::class, $ip);
        $this->assertEquals("\x7F\x00\x00\x01", $ip->getAddress());
        $this->assertEquals('127.0.0.1', $ip->getHostAddress());

        $ip = InetAddress::getByName('::1');
        $this->assertInstanceOf(Inet6Address::class, $ip);
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", $ip->getAddress());
        $this->assertEquals('::1', $ip->getHostAddress());

        $ip = InetAddress::getByName('localhost');
        $this->assertTrue($ip->getHostAddress() == '127.0.0.1');

        $ip = InetAddress::getByName('www.google.com');
        $this->assertInstanceOf(Inet4Address::class, $ip);

        $ip = InetAddress::getByName('www.google.com', InetAddress::IPV6);
        $this->assertInstanceOf(Inet6Address::class, $ip);

        $this->expectException(UnknownHostException::class);
        InetAddress::getByName('abc.def.ghi.jkl.mn.op.qr.s.t.u.v');
    }

    public function testGetAllByName()
    {
        $ips = InetAddress::getAllByName('digitalocean.com');
        $this->assertTrue(count($ips) > 2, 'Digital ocean has multiple IPv4');
        foreach ($ips as $ip) {
            $this->assertInstanceOf(Inet4Address::class, $ip);
        }
        // TODO(nathan818): Find ipv6 host to test?
    }

    public function testIsX()
    {
        foreach (self::$addresses as $hostAddress => $expectedMethods) {
            $address = InetAddress::getByName($hostAddress);
            foreach (self::$isMethods as $isMethod) {
                $result = $address->{'is'.$isMethod}();
                $this->assertEquals(in_array($isMethod, $expectedMethods), $result,
                    'hostAddress='.$hostAddress.', method='.$isMethod);
            }
        }
    }

    public function testIsReachable()
    {
        // TODO(nathan818)
    }
}
