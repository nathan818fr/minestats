<?php
namespace MinecraftPinger;

use PhpUC\IO\Stream\ByteArrayInputStream;
use PhpUC\IO\Stream\ByteArrayOutputStream;
use PhpUC\IO\Stream\DataInput;
use PhpUC\IO\Stream\DataInputStream;
use PhpUC\IO\Stream\DataOutput;
use PhpUC\IO\Stream\DataOutputStream;
use PhpUC\IO\Stream\InputStream;
use PhpUC\IO\Stream\IOException;
use PhpUC\Net\Socket\InetAddress;
use PhpUC\Net\Socket\Socket;
use PhpUC\Net\Socket\SocketException;

/**
 * PHP minecraft server pinger by Nathan Poirier (nathan818).
 */
class MinecraftPinger
{
    /**
     * @var string
     */
    private $hostname;

    /**
     * @var int
     */
    private $port;

    /**
     * A minecraft PC server pinger.
     *
     * @param string $hostname server address
     * @param int    $port     server port
     */
    public function __construct($hostname, $port = null)
    {
        $this->hostname = $hostname;
        $this->port = $port ?: 25565;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $connectTimeout       connection timeout in milliseconds
     *                                  (default: 5000)
     * @param int $communicationTimeout communication timeout in milliseconds
     *                                  (default: 5000)
     * @param int $protocolVersion      optional minecraft protocol version
     *
     * @return \stdClass the minecraft decoded json ping response
     *
     * @throws MinecraftPingException when a network error occurs or the ping
     * response is invalid
     */
    public function ping(
        $connectTimeout = null,
        $communicationTimeout = null,
        $protocolVersion = 1
    ) {
        $socket = Socket::createFromAddr(
            InetAddress::getByName($this->hostname),
            $this->port);
        try {
            $socket->connect($connectTimeout ?: 5000);
            $socket->setGlobalTimeout($communicationTimeout ?: 5000);
            $socketIn = $socket->getInputStream();
            $socketOut = new DataOutputStream($socket->getOutputStream());

            $packet = new ByteArrayOutputStream();
            $packetOut = new DataOutputStream($packet);

            // Send Handshake
            ProtocolHelper::writeVarint($packetOut, 0x00); // Packet ID - VarInt
            ProtocolHelper::writeVarint($packetOut,
                $protocolVersion); // Protocol version - VarInt
            ProtocolHelper::writeString($packetOut,
                $this->hostname); // Server Address - String
            $packetOut->writeShort($this->port); // Port - Unsigned Short
            ProtocolHelper::writeVarint($packetOut, 1); // Next State - VarInt
            $this->writePacket($socketOut, $packet);

            // Send PingRequest
            ProtocolHelper::writeVarint($packetOut, 0x00); // Packet ID - VarInt
            $this->writePacket($socketOut, $packet);

            // Read PingResponse
            $packetIn = $this->readPacket($socketIn, 65536);
            $packetId = ProtocolHelper::readVarint($packetIn);
            if ($packetId !== 0) {
                throw new MinecraftPingException('Invalid ping response packet ID');
            }

            $rawResponse = ProtocolHelper::readString($packetIn);
            $response = json_decode($rawResponse, false, 32);
            if ($response === false) {
                throw new MinecraftPingException('Invalid response json: ' .
                    json_last_error_msg());
            }

            return $response;
        } catch (IOException $e) {
            throw new MinecraftPingException('Network error', null, $e);
        } finally {
            $socket->close();
        }
    }

    private function writePacket(DataOutput $os, ByteArrayOutputStream $packet)
    {
        $data = $packet->toByteArray();
        $packet->reset();
        ProtocolHelper::writeVarint($os, strlen($data));
        $os->writeBuf($data);
    }

    private function readPacket(InputStream $is, $sizeLimit = null)
    {
        $packetLength = ProtocolHelper::readVarint(new DataInputStream($is));
        if ($sizeLimit !== null && $packetLength > $sizeLimit) {
            throw new MinecraftPingException('Too large packet response');
        }

        $readBuf = '';
        $readLen = 0;
        while ($readLen < $packetLength) {
            $buf = $is->read($packetLength - $readLen);
            if ($buf === null) {
                break;
            }
            $bufLen = strlen($buf);

            $readBuf .= $buf;
            $readLen += $bufLen;
        }

        return new DataInputStream(new ByteArrayInputStream($readBuf));
    }
}