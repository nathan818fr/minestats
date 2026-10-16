<?php
namespace PhpUC\IO\Stream;

use PHPUnit\Framework\TestCase;

class BufferedOutputStreamTest extends TestCase
{
    public function testWrite()
    {
        $bos = new ByteArrayOutputStream();
        $os = new BufferedOutputStream($bos, 333);

        $buf = '';
        $len = 1;
        for ($i = 0; $i < 24; $i++) {
            $c = '';
            for ($j = 0; $j < 10 + $len; $j++) {
                $c .= chr(rand(0, 255));
            }
            $off = ($i % 3) * 33;
            $os->write($c, $off, $len);
            $buf .= substr($c, $off, $len);
            if ($i % 50 == 0) {
                $this->assertNotEquals($buf, $bos->toByteArray());
            }
            if ($i % 33 == 0) {
                $os->flush();
                $this->assertEquals($buf, $bos->toByteArray());
            }
            $len = (int)($len * 1.5) + 1;
        }
    }
}
