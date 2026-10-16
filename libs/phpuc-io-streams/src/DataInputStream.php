<?php
namespace PhpUC\IO\Stream;

class DataInputStream extends FilterInputStream implements DataInput
{
    private static $isBigEndian;

    private $endianness;

    public function __construct(
        InputStream $is,
        $endianness = DataInput::BIG_ENDIAN
    ) {
        parent::__construct($is);
        if (!isset(self::$isBigEndian)) {
            self::$isBigEndian = (unpack('S', "\x01\x00")[1] !== 1);
        }
        $this->endianness = $endianness;
    }

    public function getEndianness()
    {
        return $this->endianness;
    }

    private function unpack($type, $len)
    {
        $buf = $this->eofRead($len);
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
        return unpack($type, $buf)[1];
    }

    private function eofRead($len = 1)
    {
        $buf = $this->read($len);
        if ($buf === null || strlen($buf) !== $len) {
            throw new EOFException();
        }
        return $buf;
    }

    public function readBoolean() : bool
    {
        return ($this->readByte() !== 0);
    }

    public function readByte() : int
    {
        return $this->unpack('c', 1);
    }

    public function readUnsignedByte() : int
    {
        return $this->unpack('C', 1);
    }

    public function readShort() : int
    {
        return $this->unpack('s', 2);
    }

    public function readUnsignedShort() : int
    {
        return $this->unpack('S', 2);
    }

    public function readInt() : int
    {
        return $this->unpack('l', 4);
    }

    public function readUnsignedInt() : int
    {
        return $this->unpack('L', 4);
    }

    public function readLong() : int
    {
        return $this->unpack('q', 8);
    }

    public function readUnsignedLong() : int
    {
        return $this->unpack('Q', 8);
    }

    public function readDouble() : int
    {
        // TODO(nathan818): Fix
        return $this->unpack('d', 8);
    }

    public function readFloat() : int
    {
        // TODO(nathan818): Fix
        return $this->unpack('f', 4);
    }

    public function readBuf($len) : string
    {
        return $this->eofRead($len);
    }

    public function skipBytes(int $n) : int
    {
        return $this->skip($n);
    }
}