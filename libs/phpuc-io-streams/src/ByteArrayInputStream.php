<?php
namespace PhpUC\IO\Stream;

class ByteArrayInputStream extends InputStream
{
    /**
     * @var string
     */
    protected $buf;
    /**
     * @var int
     */
    protected $pos;
    /**
     * @var int
     */
    protected $mark;
    /**
     * @var int
     */
    protected $size;

    public function __construct(
        string $buf,
        int $offset = 0,
        int $length = null
    ) {
        $this->buf = $buf;
        $this->pos = $offset;
        $this->mark = $offset;
        if ($length === null) {
            $this->size = strlen($buf);
        } else {
            $this->size = min($offset + $length, strlen($buf));
        }
    }

    public function read(int $len = 1)
    {
        if ($this->pos >= $this->size) {
            return null;
        }

        if ($len == 1) {
            return $this->buf[$this->pos++];
        }

        $len = min($len, $this->size - $this->pos);
        try {
            return substr($this->buf, $this->pos, $len);
        } finally {
            $this->pos += $len;
        }
    }

    public function available() : int
    {
        return $this->size - $this->pos;
    }

    public function skip(int $n) : int
    {
        $n = min($n, $this->size - $this->pos);
        $this->pos += $n;
        return $n;
    }

    public function markSupported() : bool
    {
        return true;
    }

    public function mark(int $readlimit)
    {
        $this->mark = $this->pos;
    }

    public function reset()
    {
        $this->pos = $this->mark;
    }
}