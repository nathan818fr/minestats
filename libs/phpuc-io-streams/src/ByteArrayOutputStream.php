<?php
namespace PhpUC\IO\Stream;

class ByteArrayOutputStream extends OutputStream
{
    /**
     * @var string
     */
    protected $buf = '';

    /**
     * @var int
     */
    protected $bufSize = 0;

    public function write($buf, $off = null, $len = null)
    {
        if ($this->bufSize != null) {
            $this->bufSize = null;
        }

        if ($off !== null) {
            if ($len !== null) {
                $this->buf .= substr($buf, $off, $len);
            } else {
                $this->buf .= substr($buf, $off);
            }
        } elseif ($len !== null) {
            $this->buf .= substr($buf, 0, $len);
        } else {
            $this->buf .= $buf;
        }
    }

    /**
     * Clear all currently accumulated output.
     */
    public function reset()
    {
        $this->buf = '';
        $this->bufSize = 0;
    }

    /**
     * Returns the current size of the buffer.
     *
     * @return int the current size of the buffer
     */
    public function size() : int
    {
        if ($this->bufSize == null) {
            $this->bufSize = strlen($this->buf);
        }
        return $this->bufSize;
    }

    /**
     * Returns the byte array.
     *
     * Bytes are stored on php string. Use DataOutputStream to read bytes as
     * int.
     *
     * @return string the byte array
     */
    public function toByteArray() : string
    {
        return $this->buf;
    }

    public function __toString()
    {
        return $this->buf;
    }
}