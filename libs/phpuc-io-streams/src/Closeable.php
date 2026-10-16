<?php
namespace PhpUC\IO\Stream;

interface Closeable
{
    /**
     * Closes this stream and releases any system resources associated with it.
     *
     * @return void
     * @throws IOException
     */
    public function close();
}