<?php
namespace PhpUC\IO\Stream;

use PHPUnit\Framework\TestCase;

// TODO(nathan818): Add tests for litle-endian
class DataInputStreamTest extends TestCase
{
    // java-generator start

    private static $testBufferBoolean = "\x01\x00";
    private static $testBufferByte = "\x00\x01\xff\x11\x7f\x80\xf3";
    private static $testBufferUnsignedByte = "\x00\x01\x80\x11\x7f\xff\xb0";
    private static $testBufferShort = "\x00\x00\x00\x01\xff\xff\x05\xb0\x7f\xff\x9e\x20\x80\x00";
    private static $testBufferInt = "\x00\x00\x00\x00\x00\x00\x00\x01\xff\xff\xff\xff\x00\x06\x7f\x67\x7f\xff\xff\xff\xff\xf0\xeb\x5e\x80\x00\x00\x00";
    private static $testBufferLong = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x19\x5d\x9e\x87\x7f\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x19\xe0\x17\xca\x1e\x80\x00\x00\x00\x00\x00\x00\x00";
    private static $testBufferDouble = "\x00\x00\x00\x00\x00\x00\x00\x00\x3f\xf0\x00\x00\x00\x00\x00\x00\xbf\xf0\x00\x00\x00\x00\x00\x00\x3f\xee\xc8\xb4\x39\x58\x10\x62\xbf\xc3\xd7\x0a\x3d\x70\xa3\xd7\x7f\xf0\x00\x00\x00\x00\x00\x00\x7f\xf8\x00\x00\x00\x00\x00\x00\xff\xf0\x00\x00\x00\x00\x00\x00";
    private static $testBufferFloat = "\x00\x00\x00\x00\x3f\x80\x00\x00\xbf\x80\x00\x00\x3f\x76\x45\xa2\xbe\x1e\xb8\x52\x7f\x80\x00\x00\xff\x80\x00\x00\x7f\xc0\x00\x00";

    // java-generator end

    protected function createInputStream($buffer)
    {
        return new DataInputStream(new ByteArrayInputStream($buffer));
    }

    public function testReadBoolean()
    {
        $is = $this->createInputStream(self::$testBufferBoolean);
        $this->assertEquals(true, $is->readBoolean());
        $this->assertEquals(false, $is->readBoolean());
    }

    public function testReadByte()
    {
        $is = $this->createInputStream(self::$testBufferByte);
        $this->assertEquals(0, $is->readByte());
        $this->assertEquals(1, $is->readByte());
        $this->assertEquals(-1, $is->readByte());
        $this->assertEquals(17, $is->readByte());
        $this->assertEquals(127, $is->readByte());
        $this->assertEquals(-128, $is->readByte());
        $this->assertEquals(-13, $is->readByte());
    }

    public function testReadUnsignedByte()
    {
        $is = $this->createInputStream(self::$testBufferUnsignedByte);
        $this->assertEquals(0, $is->readUnsignedByte());
        $this->assertEquals(1, $is->readUnsignedByte());
        $this->assertEquals(128, $is->readUnsignedByte());
        $this->assertEquals(17, $is->readUnsignedByte());
        $this->assertEquals(127, $is->readUnsignedByte());
        $this->assertEquals(255, $is->readUnsignedByte());
        $this->assertEquals(176, $is->readUnsignedByte());
    }

    public function testReadShort()
    {
        $is = $this->createInputStream(self::$testBufferShort);
        $this->assertEquals(0, $is->readShort());
        $this->assertEquals(1, $is->readShort());
        $this->assertEquals(-1, $is->readShort());
        $this->assertEquals(1456, $is->readShort());
        $this->assertEquals(32767, $is->readShort());
        $this->assertEquals(-25056, $is->readShort());
        $this->assertEquals(-32768, $is->readShort());
    }

    public function testReadUnsignedShort()
    {
        $is = $this->createInputStream(self::$testBufferShort);
        $this->assertEquals(0, $is->readUnsignedShort());
        $this->assertEquals(1, $is->readUnsignedShort());
        $this->assertEquals(65535, $is->readUnsignedShort());
        $this->assertEquals(1456, $is->readUnsignedShort());
        $this->assertEquals(32767, $is->readUnsignedShort());
        $this->assertEquals(40480, $is->readUnsignedShort());
        $this->assertEquals(32768, $is->readUnsignedShort());
    }

    public function testReadInt()
    {
        $is = $this->createInputStream(self::$testBufferInt);
        $this->assertEquals(0, $is->readInt());
        $this->assertEquals(1, $is->readInt());
        $this->assertEquals(-1, $is->readInt());
        $this->assertEquals(425831, $is->readInt());
        $this->assertEquals(2147483647, $is->readInt());
        $this->assertEquals(-988322, $is->readInt());
        $this->assertEquals(-2147483648, $is->readInt());
    }

    public function testReadUnsignedInt()
    {
        $is = $this->createInputStream(self::$testBufferInt);
        $this->assertEquals(0, $is->readUnsignedInt());
        $this->assertEquals(1, $is->readUnsignedInt());
        $this->assertEquals(4294967295, $is->readUnsignedInt());
        $this->assertEquals(425831, $is->readUnsignedInt());
        $this->assertEquals(2147483647, $is->readUnsignedInt());
        $this->assertEquals(4293978974, $is->readUnsignedInt());
        $this->assertEquals(2147483648, $is->readUnsignedInt());
    }

    public function testReadLong()
    {
        // Long works only on 64-bits machines
        if (PHP_INT_SIZE < 8) {
            return;
        }

        $is = $this->createInputStream(self::$testBufferLong);
        $this->assertEquals(0, $is->readLong());
        $this->assertEquals(1, $is->readLong());
        $this->assertEquals(-1, $is->readLong());
        $this->assertEquals(425565831, $is->readLong());
        $this->assertEquals(9223372036854775807, $is->readLong());
        $this->assertEquals(-988377789922, $is->readLong());
        $this->assertEquals(-9223372036854775808, $is->readLong());
    }

    public function testReadUnsignedLong()
    {
        // PHP does not support unsigned long
        // So, useless tests are commented

        $is = $this->createInputStream(self::$testBufferLong);
        $this->assertEquals(0, $is->readUnsignedLong());
        $this->assertEquals(1, $is->readUnsignedLong());
        $is->skipBytes(8); // $this->assertEquals(1.84467441e19, $is->readUnsignedLong());
        $this->assertEquals(425565831, $is->readUnsignedLong());
        $this->assertEquals(9223372036854775807, $is->readUnsignedLong());
        $is->skipBytes(8); // $this->assertEquals(1.84467431e19, $is->readUnsignedLong());
        $is->skipBytes(8); // $this->assertEquals(9.22337204e18, $is->readUnsignedLong());
    }

    public function testDouble()
    {
        // TODO: Fix readDouble
        return;

        $is = $this->createInputStream(self::$testBufferDouble);
        $this->assertEquals(0, $is->readDouble());
        $this->assertEquals(1, $is->readDouble());
        $this->assertEquals(-1, $is->readDouble());
        $this->assertEquals(0.962, $is->readDouble());
        $this->assertEquals(0.155, $is->readDouble());
    }

    public function testFloat()
    {
        // TODO: Fix readFloat
        return;

        $is = $this->createInputStream(self::$testBufferFloat);
        $this->assertEquals(0, $is->readFloat());
        $this->assertEquals(1, $is->readFloat());
        $this->assertEquals(-1, $is->readFloat());
        $this->assertEquals(0.962, $is->readFloat());
        $this->assertEquals(0.155, $is->readFloat());
    }
}
