<?php
namespace PhpUC\IO\Stream;

use PHPUnit\Framework\TestCase;

class ByteArrayInputStreamTest extends TestCase
{
    const TESTBUFFER_SIZE = 1024;
    protected $testBuffer;

    public function __construct()
    {
        parent::__construct();
        $this->testBuffer = '';
        for ($i = 0; $i < self::TESTBUFFER_SIZE; $i++) {
            $this->testBuffer .= pack('c', rand(0, 255));
        }
    }

    protected function createInputStream()
    {
        return new ByteArrayInputStream($this->testBuffer);
    }

    public function testRead()
    {
        // Single byte read
        $is = $this->createInputStream();
        for ($i = 0; $i < self::TESTBUFFER_SIZE; $i++) {
            $this->assertEquals($this->testBuffer[$i], $is->read(),
                '$i=' . $i);
        }
        $this->assertNull($is->read());

        // Multi-bytes read
        $is = $this->createInputStream();
        $bufferPos = 0;

        $len = 2;
        while ($bufferPos < self::TESTBUFFER_SIZE) {
            $buf = $is->read($len);
            $this->assertEquals(substr($this->testBuffer, $bufferPos, $len),
                $buf, '$bufferPos=' . $bufferPos . ', $len=' . $len);
            $bufferPos += $len;
            $len++;
        }
        $this->assertNull($is->read());

        // Full (too large) read
        $is = $this->createInputStream();
        $this->assertEquals($this->testBuffer,
            $is->read(self::TESTBUFFER_SIZE * 10));
        $this->assertNull($is->read());
    }

    public function testAvailable()
    {
        $is = $this->createInputStream();
        $bufferPos = 0;
        $len = 1;

        $this->assertEquals(self::TESTBUFFER_SIZE, $is->available());
        while ($bufferPos < self::TESTBUFFER_SIZE) {
            $buf = $is->read($len);
            $bufferPos += strlen($buf);
            $len++;
            $this->assertEquals(self::TESTBUFFER_SIZE - $bufferPos,
                $is->available());
        }
        $this->assertEquals(0, $is->available());
    }

    public function testSkip()
    {
        // Read and skip
        $is = $this->createInputStream();
        $skipLen = 0;
        for ($i = 0; $i < self::TESTBUFFER_SIZE; $i++) {
            $skipped = $is->skip($skipLen);
            $this->assertEquals(min($skipLen, self::TESTBUFFER_SIZE - $i),
                $skipped);
            $i += $skipped;
            $skipLen = (int)($skipLen * 1.5) + 1;
            if ($i < self::TESTBUFFER_SIZE) {
                $this->assertEquals($this->testBuffer[$i], $is->read(),
                    '$i=' . $i);
            }
        }
        $this->assertNull($is->read());

        // Skip all
        $is = $this->createInputStream();
        $is->skip(self::TESTBUFFER_SIZE);
        $this->assertNull($is->read());
    }

    public function testMarkAndReset()
    {
        $skip = (int)(self::TESTBUFFER_SIZE / 3);

        // Skip, mark, reset
        $is = $this->createInputStream();
        $is->skip($skip);
        $is->mark(self::TESTBUFFER_SIZE);
        $this->assertEquals($this->testBuffer[$skip], $is->read());
        $is->reset();
        $this->assertEquals($this->testBuffer[$skip], $is->read());
        $this->assertEquals($this->testBuffer[$skip + 1], $is->read());

        // Mark, skip, reset
        $is = $this->createInputStream();
        $is->skip($skip);
        $is->mark(self::TESTBUFFER_SIZE);
        $is->skip($skip);
        $this->assertEquals($this->testBuffer[$skip * 2], $is->read());
        $is->reset();
        $this->assertEquals($this->testBuffer[$skip], $is->read());
        $this->assertEquals(substr($this->testBuffer, $skip + 1, 16),
            $is->read(16));
        $this->assertEquals(substr($this->testBuffer, $skip + 1 + 16, 512),
            $is->read(512));
    }
}
