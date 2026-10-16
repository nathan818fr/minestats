<?php
namespace PhpUC\IO\Stream;

class FilterInputStreamTest extends ByteArrayInputStreamTest
{
    protected function createInputStream()
    {
        return new FilterInputStream(parent::createInputStream());
    }
}
