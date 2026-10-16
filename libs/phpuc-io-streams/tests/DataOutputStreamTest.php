<?php
namespace PhpUC\IO\Stream;

use PHPUnit\Framework\TestCase;

class DataOutputStreamTest extends TestCase
{
    private function doWriteTest(
        $values,
        $writeMethod,
        $readMethod = null,
        $readLen = false
    ) {
        if ($readMethod === null) {
            $readMethod = $writeMethod;
        }

        $bos = new ByteArrayOutputStream();
        $os = new DataOutputStream($bos);
        foreach ($values as $v) {
            if (is_array($v)) {
                $os->{'write' . $writeMethod}($v[0]);
            } else {
                $os->{'write' . $writeMethod}($v);
            }
        }

        $is = new DataInputStream(new ByteArrayInputStream($bos->toByteArray()));
        foreach ($values as $v) {
            if (is_array($v)) {
                $v = $v[1];
            }
            if ($readLen) {
                $this->assertEquals($v, $is->{'read' . $readMethod}(strlen($v)));
            } else {
                $this->assertEquals($v, $is->{'read' . $readMethod}());
            }
        }

        $this->assertEquals(null, $is->read());
    }

    public function testBoolean()
    {
        $this->doWriteTest([
            true,
            false,
            [0, false],
            [1, true]
        ], 'Boolean');
    }

    public function testByte()
    {
        $this->doWriteTest([
            0,
            1,
            -1,
            17,
            127,
            -128,
            -13,
            [134744067, 3]
        ], 'Byte');

        $this->doWriteTest([
            0,
            1,
            255,
            17,
            127,
            128,
            243,
            [134744067, 3]
        ], 'Byte', 'UnsignedByte');
    }

    public function testShort()
    {
        $this->doWriteTest([
            0,
            1,
            -1,
            1456,
            32767,
            -25056,
            -32768,
            [538971139, 3075]
        ], 'Short');

        $this->doWriteTest([
            0,
            1,
            65535,
            1456,
            32767,
            40480,
            32768,
            [538971139, 3075]
        ], 'Short', 'UnsignedShort');
    }

    public function testInt()
    {
        $this->doWriteTest([
            0,
            1,
            -1,
            14578236,
            2147483647,
            -2505634,
            -2147483648,
            [18256759811, 1076890627]
        ], 'Int');

        $this->doWriteTest([
            0,
            1,
            4294967295,
            14578236,
            2147483647,
            4292461662,
            2147483648,
            [18256759811, 1076890627]
        ], 'Int', 'UnsignedInt');
    }

    public function testLong()
    {
        $this->doWriteTest([
            0,
            1,
            -1,
            14578236,
            9223372036854775807,
            -2505634,
            -9223372036854775808,
            18256759811
        ], 'Long');

        // PHP does not support unsigned long
        // So, useless tests are commented
        $this->doWriteTest([
            0,
            1,
            // -1,
            14578236,
            9223372036854775807,
            // -2505634,
            // -9223372036854775808,
            18256759811
        ], 'Long', 'UnsignedLong');
    }

    public function testDouble()
    {
        // TODO: Fix writeDouble
    }

    public function testFloat()
    {
        // TODO: Fix writeFloat
    }

    public function testWrite()
    {
        $this->doWriteTest([
            'a',
            '9',
            "\x01",
            "\x00",
            "\xE1",
            'somestring'
        ], 'Buf', null, true);
    }
}
