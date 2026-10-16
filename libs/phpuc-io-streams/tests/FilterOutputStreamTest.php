<?php
namespace PhpUC\IO\Stream;

class FilterOutputStreamTest extends ByteArrayOutputStreamTest
{
    protected function createFilterStream($bos) : OutputStream
    {
        return new FilterOutputStream($bos);
    }
}
