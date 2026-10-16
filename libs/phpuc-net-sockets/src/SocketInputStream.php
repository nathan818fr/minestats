<?php
namespace PhpUC\Net\Socket;

use PhpUC\IO\Stream\InputStream;

class SocketInputStream extends InputStream
{
    /**
     * @var Socket
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $eof;

    function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public function read(int $len = 1)
    {
        $this->socket->checkGlobalTimeoutStart('Read');
        try {
            $buf = @socket_read($this->socket->getResource(), $len);
            if ($buf === false) {
                $this->socket->throwSocketError();
            }
        }
        finally {
            $this->socket->checkGlobalTimeoutStop(false);
        }

        return $buf;
    }

    public function close()
    {
        parent::close();
        if (!$this->socket->isClosed()) {
            $this->socket->close();
        }
    }
}