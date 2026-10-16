<?php
namespace PhpUC\Net\Socket;

use PhpUC\IO\Stream\OutputStream;

class SocketOutputStream extends OutputStream
{
    /**
     * @var Socket
     */
    protected $socket;

    function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public function write($buf, $off = null, $len = null)
    {
        if ($off !== null) {
            $buf = substr($buf, $off);
        }
        if ($len !== null) {
            $buf = substr($buf, 0, $len);
        }

        $this->socket->checkGlobalTimeoutStart('Write');
        try {
            if (@socket_write($this->socket->getResource(), $buf) === false) {
                $this->socket->throwSocketError();
            }
        }
        finally {
            $this->socket->checkGlobalTimeoutStop(false);
        }
    }

    public function close()
    {
        parent::close();
        if (!$this->socket->isClosed()) {
            $this->socket->close();
        }
    }
}