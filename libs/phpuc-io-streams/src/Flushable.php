<?php
namespace PhpUC\IO\Stream;

interface Flushable
{
    /**
     * Flushes this stream by writing any buffered output to the underlying stream.
     *
     * @return void
     * @throws IOException
     */
    public function flush();
}