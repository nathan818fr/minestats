<?php
namespace PhpUC\IO\Stream;

abstract class OutputStream implements Closeable, Flushable
{
    /**
     * Writes bytes from the specified buffer to this output stream.
     *
     * @param string $buf
     * @param null|int $off
     * @param null|int $len
     * @return void
     */
    public abstract function write($buf, $off = null, $len = null);

    /**
     * @inheritdoc
     */
    public function flush()
    {
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
    }
}