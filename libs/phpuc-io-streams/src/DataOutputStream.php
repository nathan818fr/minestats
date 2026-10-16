<?php
namespace PhpUC\IO\Stream;

class DataOutputStream extends FilterOutputStream implements DataOutput
{
    private static $isBigEndian;

    private $endianness;

    public function __construct(
        OutputStream $out,
        $endianness = DataOutput::BIG_ENDIAN
    ) {
        parent::__construct($out);
        if (!isset(self::$isBigEndian)) {
            self::$isBigEndian = (unpack('S', "\x01\x00")[1] !== 1);
        }
        $this->endianness = $endianness;
    }

    public function getEndianness()
    {
        return $this->endianness;
    }

    private function pack($type, $value, $len)
    {
        $buf = pack($type, $value);
        if ($len !== 1) {
            if ($this->endianness == DataInput::BIG_ENDIAN) {
                $rev = !self::$isBigEndian;
            } else {
                $rev = self::$isBigEndian;
            }
            if ($rev) {
                $buf = strrev($buf);
            }
        }
        $this->write($buf);
    }

    public function writeBoolean($boolean)
    {
        $this->writeByte($boolean ? 1 : 0);
    }

    public function writeByte($byteValue)
    {
        $this->pack('c', $byteValue & 0xFF, 1);
    }

    public function writeShort($shortValue)
    {
        $this->pack('s', $shortValue & 0xFFFF, 2);
    }

    public function writeInt($intValue)
    {
        $this->pack('l', $intValue & 0xFFFFFFFF, 4);
    }

    public function writeLong($longValue)
    {
        $this->pack('q', $longValue, 8);
    }

    public function writeDouble($doubleValue)
    {
        // TODO(nathan818): Fix
        $this->pack('d', $doubleValue, 8);
    }

    public function writeFloat($floatValue)
    {
        // TODO(nathan818): Fix
        $this->pack('d', $floatValue, 4);
    }

    public function writeBuf($buf)
    {
        $this->write($buf);
    }
}