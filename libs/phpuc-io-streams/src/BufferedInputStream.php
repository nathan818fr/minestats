<?php
namespace PhpUC\IO\Stream;

class BufferedInputStream extends FilterInputStream
{
    const DEFAULT_BUFFER_SIZE = 8192;

    protected $buf = null;
    protected $bufSize;
    protected $count = 0;
    protected $pos;
    protected $markpos = -1;
    protected $marklimit;

    public function __construct(InputStream $in, int $size = null)
    {
        parent::__construct($in);
        if ($size !== null && $size <= 0) {
            throw new \InvalidArgumentException("Buffer size <= 0");
        }
        $this->bufSize = $size ?: self::DEFAULT_BUFFER_SIZE;
    }

    private function getInIfOpen() : InputStream
    {
        if ($this->in == null) {
            throw new IOException("Stream closed");
        }
        return $this->in;
    }

    private function getBufIfOpen()
    {
        if ($this->in == null) {
            throw new IOException("Stream closed");
        }
        return $this->buf;
    }

    private function fill()
    {
        if ($this->markpos >= 0 && $this->pos > $this->markpos + $this->marklimit) {
            $this->markpos = -1;
        }

        if ($this->markpos < 0) {
            $this->pos = 0;
            $this->buf = $this->getInIfOpen()->read($this->bufSize);
            if ($this->buf === null) {
                $this->count = 0;
                return null;
            }
            $this->count = strlen($this->buf);
            return $this->count;
        } else {
            $newBuf = $this->getInIfOpen()->read($this->bufSize);
            if ($newBuf === null) {
                return null;
            }
            $newBufCount = strlen($newBuf);
            $this->buf .= $newBuf;
            $this->count += $newBufCount;
            return $newBufCount;
        }
    }

    private function readAndIncrement($maxLen, &$readCounter = null)
    {
        $len = min($this->count - $this->pos, $maxLen);
        if ($len <= 0) {
            return '';
        }
        if ($len === 1) {
            if ($readCounter !== null) {
                $readCounter++;
            }
            return $this->buf[$this->pos++];
        }
        try {
            if ($readCounter !== null) {
                $readCounter += $len;
            }
            return substr($this->buf, $this->pos, $len);
        } finally {
            $this->pos += $len;
        }
    }

    public function read(int $len = 1)
    {
        if ($this->pos + $len <= $this->count) {
            return $this->readAndIncrement($len);
        }

        $result = null;
        $resultLen = 0;
        while (true) {
            if ($this->buf === null || $this->pos >= $this->count) {
                if ($this->fill() === null) {
                    return $result;
                }
            }

            if ($result === null) {
                $result = '';
            }

            $result .= $this->readAndIncrement($len - $resultLen, $resultLen);
            if ($resultLen >= $len) {
                return $result;
            }
        }
    }

    public function available() : int
    {
        return $this->count - $this->pos + $this->getInIfOpen()->available();
    }

    public function skip(int $n) : int
    {
        if ($n <= 0) {
            return 0;
        }

        if ($this->markpos >= 0) {
            while ($this->pos + $n >= $this->count) {
                if ($this->fill() === null) {
                    break;
                }
            }
            $n = min($n, $this->count - $this->pos);
            $this->pos += $n;
            return $n;
        }

        if ($this->pos + $n < $this->count) {
            $this->pos += $n;
            return $n;
        } else {
            $skipped = 0;
            while ($n > 0) {
                if ($this->pos + $n >= $this->count) {
                    $skipped += ($this->count - $this->pos);
                    $n -= ($this->count - $this->pos);
                    if ($this->fill() === null) {
                        break;
                    }
                } else {
                    $this->pos += $n;
                    $skipped += $n;
                    $n = 0;
                }
            }
            return ($skipped);
        }
    }

    public function close()
    {
        $this->getInIfOpen()->close();
        $this->in = null;
        $this->buf = null;
    }

    public function markSupported() : bool
    {
        return true;
    }

    public function mark(int $readlimit)
    {
        $this->marklimit = $readlimit;
        $this->markpos = $this->pos;
    }

    public function reset()
    {
        if ($this->markpos < 0) {
            throw new IOException("Resetting to invalid mark");
        }

        $this->getBufIfOpen();
        $this->pos = $this->markpos;
    }
}