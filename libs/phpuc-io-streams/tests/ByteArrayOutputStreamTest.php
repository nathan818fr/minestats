<?php
namespace PhpUC\IO\Stream;

use PHPUnit\Framework\TestCase;

class ByteArrayOutputStreamTest extends TestCase
{
    protected function createByteStream() : ByteArrayOutputStream
    {
        return new ByteArrayOutputStream();
    }

    protected function createFilterStream($bos) : OutputStream
    {
        return $bos;
    }

    public function testWriteAndToByteArray()
    {
        $bos = $this->createByteStream();
        $fos = $this->createFilterStream($bos);

        $buf = '';

        for ($i = 0; $i < 124; $i++) {
            $c = '';
            for ($j = 0; $j < 10; $j++) {
                $c .= chr(rand(0, 255));
            }
            $off = $i % 9;
            $len = min($i % 7, 10 - $off);
            $fos->write($c, $off, $len);
            $buf .= substr($c, $off, $len);
            if ($i % 33 == 0) {
                $this->assertEquals($buf, $bos->toByteArray());
            }
        }

        $this->assertEquals($buf, $bos->toByteArray());
    }

    public function testReset()
    {
        $bos = $this->createByteStream();
        $fos = $this->createFilterStream($bos);

        $fos->write('uselessstring');
        $bos->reset();
        $fos->write("\xAA\x25");
        $this->assertEquals("\xAA\x25", $bos->toByteArray());
        // TODO: Longer test
    }

    public function testSize()
    {
        $bos = $this->createByteStream();
        $fos = $this->createFilterStream($bos);

        $fos->write('uselessstring');
        $this->assertEquals(13, $bos->size());
        $fos->write("\xAA\x25");
        $this->assertEquals(15, $bos->size());
        $bos->reset();
        $this->assertEquals(0, $bos->size());
        $fos->write("\xAA\x25");
        $this->assertEquals(2, $bos->size());
        // TODO: Longer test
    }
}
