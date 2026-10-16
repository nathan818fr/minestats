<?php
namespace PhpUC\IO\Stream;

class BufferedInputStreamTest extends ByteArrayInputStreamTest
{
    protected function createInputStream()
    {
        return new BufferedInputStream(parent::createInputStream(),
            (int)max(1, self::TESTBUFFER_SIZE / 4.33));
    }

    public function testMarkAndReset()
    {
        parent::testMarkAndReset();
        // TODO(nathan818): Test readLimit
    }


}
