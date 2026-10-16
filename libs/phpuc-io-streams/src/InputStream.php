<?php
namespace PhpUC\IO\Stream;

/**
 * Superclass for every input stream of bytes.
 */
abstract class InputStream implements Closeable
{
    const MAX_SKIP_BUFFER_SIZE = 2048;

    /**
     * Reads the next $len byte(s) of data from the input stream.
     * If no byte is available because the end of the stream has been reached,
     * null is returned.
     * This method blocks until input data is available, the end of the stream
     * is detected, or an exception is thrown.
     *
     * Bytes are stored on php string. Use DataInputStream to read bytes as int.
     *
     * @param int $len number of bytes to read
     * @return null|string the bytes string; or null if there is no more data because
     * the end of the stream has been reached
     * @throws IOException
     * @see DataInputStream
     */
    public abstract function read(int $len = 1);

    /**
     * Returns an estimate of the number of bytes that can be read (or skipped
     * over) from this input stream without blocking by the next invocation of a
     * method for this input stream.
     *
     * @return int an estimate of the number of bytes that can be read (or
     * skipped over) from this input stream without blocking or 0 when it
     * reaches the end of the input stream
     * @throws IOException
     */
    public function available() : int
    {
        return 0;
    }

    /**
     * Skips over and discards n bytes of data from this input stream.
     *
     * @param int $n the number of bytes to be skipped
     * @return int the actual number of bytes skipped
     * @throws IOException
     */
    public function skip(int $n) : int
    {
        if ($n <= 0) {
            return 0;
        }

        $skipped = 0;
        $skipLimit = min(self::MAX_SKIP_BUFFER_SIZE, $n);
        while ($skipped < $n) {
            $buf = $this->read(min($skipLimit, $n - $skipped));
            if ($buf === null) {
                break;
            }
            $skipped += strlen($buf);
        }
        return $skipped;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
    }

    /**
     * Tests if this input stream supports the mark and reset methods.
     *
     * @return bool true if this stream instance supports the mark and reset
     * methods; false otherwise.
     */
    public function markSupported() : bool
    {
        return false;
    }

    /**
     * Marks the current position in this input stream.
     *
     * A subsequent call to the reset method repositions this stream at the last
     * marked position so that subsequent reads re-read the same bytes.
     *
     * The readlimit arguments tells this input stream to allow that many bytes
     * to be read before the mark position gets invalidated.
     *
     * @param int $readlimit
     */
    public function mark(int $readlimit)
    {
    }

    /**
     * Repositions this stream to the position at the time the mark method was
     * last called on this input stream.
     *
     * @throws IOException
     */
    public function reset()
    {
        throw new IOException("mark and reset not supported");
    }
}