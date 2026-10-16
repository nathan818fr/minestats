<?php
namespace PhpUC\IO\Stream;

class BufferedOutputStream extends FilterOutputStream
{
    const DEFAULT_BUFFER_SIZE = 8192;

    protected $buf = null;
    protected $bufSize;
    protected $count = 0;

    public function __construct(OutputStream $out, int $size = null)
    {
        parent::__construct($out);
        if ($size !== null && $size <= 0) {
            throw new \InvalidArgumentException("Buffer size <= 0");
        }
        $this->bufSize = $size ?: self::DEFAULT_BUFFER_SIZE;
    }

    private function flushBuffer()
    {
        if ($this->count > 0) {
            $this->out->write($this->buf);
            $this->buf = null;
            $this->count = 0;
        }
    }

    public function write($buf, $off = null, $len = null)
    {
        $partLen = strlen($buf);
        if ($off !== null) {
            $partLen -= $off;
        }
        if ($len !== null && $len < $partLen) {
            $partLen = $len;
        }

        if ($partLen <= 0) {
            return;
        }

        if ($this->count + $partLen >= $this->bufSize) {
            $this->flushBuffer();
            if ($partLen >= $this->bufSize) {
                $this->out->write(substr($buf, $off, $this->bufSize));
                $partLen -= $this->bufSize;
                $off += $this->bufSize;
            }
            if ($partLen <= 0) {
                return;
            }
        }

        $this->buf .= substr($buf, $off, $partLen);
        $this->count += $partLen;
    }

    public function flush()
    {
        $this->flushBuffer();
        $this->out->flush();
    }
}