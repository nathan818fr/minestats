<?php
namespace PhpUC\IO\Stream;

class FilterOutputStream extends OutputStream
{
    /**
     * @var OutputStream
     */
    protected $out;

    public function __construct(OutputStream $out)
    {
        $this->out = $out;
    }

    public function write($buf, $off = null, $len = null)
    {
        $this->out->write($buf, $off, $len);
    }

    public function flush()
    {
        $this->out->flush();
    }

    public function close()
    {
        $this->out->close();
    }
}