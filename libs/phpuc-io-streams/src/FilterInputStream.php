<?php
namespace PhpUC\IO\Stream;

class FilterInputStream extends InputStream
{
    /**
     * @var InputStream
     */
    protected $in;

    public function __construct(InputStream $in)
    {
        $this->in = $in;
    }

    public function read(int $len = 1)
    {
        return $this->in->read($len);
    }

    public function available() : int
    {
        return $this->in->available();
    }

    public function skip(int $n) : int
    {
        return $this->in->skip($n);
    }

    public function close()
    {
        $this->in->close();
    }

    public function markSupported() : bool
    {
        return $this->in->markSupported();
    }

    public function mark(int $readlimit)
    {
        $this->in->mark($readlimit);
    }

    public function reset()
    {
        $this->in->reset();
    }
}