<?php
namespace PhpUC\Net\Socket;

use PhpUC\IO\Stream\Closeable;
use PhpUC\IO\Stream\InputStream;
use PhpUC\IO\Stream\IOException;
use PhpUC\IO\Stream\OutputStream;
use SebastianBergmann\CodeCoverage\RuntimeException;

/**
 * Client or server socket supporting different protocols.
 *
 * Unlike Java, where the socket for TCP client or server, and UDP are separated, everything is grouped in this class
 * which wraps the PHP socket extension.
 *
 * It's recommended to use static methods (liek createFromAddr) to create a socket easily.
 */
class Socket implements Closeable
{
    public static function createFromAddr(InetAddress $address, int $port, $protocol = null, $type = null)
    {
        if ($address instanceof Inet4Address) {
            $domain = AF_INET;
        } elseif ($address instanceof Inet6Address) {
            $domain = AF_INET6;
        } else {
            throw new \InvalidArgumentException('Unknown address type');
        }

        return new Socket($domain, $type, $protocol, $address, $port);
    }

    /**
     * @var InetAddress
     */
    protected $addr;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $blocking = true;

    /**
     * @var InputStream
     */
    protected $in;

    /**
     * @var OutputStream
     */
    protected $out;

    /*
     * Global timeout fields
     */

    /**
     * @var int
     */
    protected $globalTimeout = null;

    protected $globalTimeoutCurrentOperation;

    protected $globalTimeoutStartTime;

    protected $globalTimeoutOrigRead;

    protected $globalTimeoutOrigWrite;

    /**
     * Create a socket (endpoint for communication).
     *
     * @param int|resource $domain   a socket resource or the protocol family to be used by the socket (AF_INET,
     *                               AF_INET6, AF_UNIX)
     * @param int          $type     the type of communication to be used by the socket (SOCK_STREAM, ...)
     * @param int|string   $protocol the protocol (SOL_TCP, SOL_UDP or protocol name as string)
     * @param InetAddress  $addr     optional address that will be used during calls to connect([timeout]);
     * @param int          $port     optional port that will be used during calls to connect([timeout]);
     *
     * @link http://php.net/manual/en/function.socket-create.php
     *
     * @throws SocketException if it's impossible to create socket
     */
    public function __construct($domain, $type = null, $protocol = null, $addr = null, $port = null)
    {
        if ($domain !== null && is_resource($domain) && $type === null && $protocol === null) {
            $this->socket = $domain;
        } else {
            if ($protocol === null) {
                $protocol = SOL_TCP;
            } elseif (is_string($protocol)) {
                $protocolStr = $protocol;
                $protocol = @getprotobyname($protocolStr);
                if ($protocol === false) {
                    throw new \InvalidArgumentException('Unknown protocol: '.$protocolStr);
                }
            }

            if ($type === null) {
                $type = SOCK_STREAM;
            }

            $this->socket = @socket_create($domain, $type, $protocol);
            if ($this->socket === false) {
                $this->throwSocketError();
            }
        }
        $this->addr = $addr;
        $this->port = $port;
        $this->in = new SocketInputStream($this);
        $this->out = new SocketOutputStream($this);
    }

    function throwSocketError()
    {
        $socketErrCode = socket_last_error();
        if ($socketErrCode === defined('SOCKET_EAGAIN') ? SOCKET_EAGAIN : SOCKET_EWOULDBLOCK) {
            if (!$this->blocking) {
                throw new SocketTryAgain();
            } else {
                $this->checkGlobalTimeoutStop(true);
            }
        }
        throw new SocketException('Socket error: '.socket_strerror($socketErrCode), $socketErrCode);
    }

    function checkGlobalTimeoutStart($operation)
    {
        if ($this->globalTimeout !== null) {
            if ($this->globalTimeoutCurrentOperation !== null) {
                throw new RuntimeException('Previous global timeout check is already running!');
            }

            // Save current operation timeout
            $reqTimeout = $this->{'get'.$operation.'Timeout'}();
            $this->{'globalTimeoutOrig'.$operation} = $reqTimeout;

            // Apply operation timeout
            if ($reqTimeout === 0 || $reqTimeout > $this->globalTimeout) {
                $this->{'set'.$operation.'Timeout'}($this->globalTimeout);
            }

            // Save start time
            $this->globalTimeoutCurrentOperation = $operation;
            $this->globalTimeoutStartTime = microtime(true);
        }
    }

    function checkGlobalTimeoutStop($onTimeout)
    {
        $endTime = microtime(true);
        if ($this->globalTimeoutCurrentOperation !== null) {
            $operation = $this->globalTimeoutCurrentOperation;
            $this->globalTimeoutCurrentOperation = null;

            // Reset current operation timeout
            $reqTimeout = $this->{'globalTimeoutOrig'.$operation};
            $this->{'set'.$operation.'Timeout'}($reqTimeout);

            // Reduce timeout
            if ($onTimeout) {
                $this->globalTimeout = 0;
                throw new SocketException('Global timeout reached!', socket_last_error());
            } else {
                $elapsedTime = ceil(($endTime - $this->globalTimeoutStartTime) * 1000);
                $this->globalTimeout -= $elapsedTime;
                if ($this->globalTimeout < 1) {
                    $this->globalTimeout = 1;
                }
            }
        }
    }

    /**
     * Returns the PHP socket resource.
     *
     * It's unsafe and can break state of this object, so use only if you know what you're doing!
     *
     * @return resource the PHP socket resource
     */
    public function getResource()
    {
        return $this->socket;
    }

    /**
     * Binds a name to this socket.
     * @link http://php.net/manual/en/function.socket-bind.php
     *
     * @param InetAddress $addr
     * @param int         $port
     *
     * @throws SocketException
     */
    public function bind(InetAddress $addr, $port = 0)
    {
        if (@socket_bind($this->socket, $addr->getHostAddress(), $port) === false) {
            $this->throwSocketError();
        }
    }

    /**
     * Initiates a connection on this socket.
     *
     * connect([$addr, $port][, $timeout]);
     *
     * @param InetAddress $addr    (optional if specified during construction - but required if port is specified)
     * @param int         $port    (optional if specified during construction - but required if address is specified)
     * @param int         $timeout connection timeout in milliseconds (optional - incompatible with non-blocking socket)
     *
     * @throws SocketException
     */
    public function connect($addr = null, $port = null, $timeout = null)
    {
        if ($port == null && $timeout == null) {
            if ($addr !== null) {
                $timeout = $addr;
            } else {
                $timeout = 0;
            }
            if ($this->addr == null || $this->port == null) {
                throw new \InvalidArgumentException('Address and port were not specified during construction!');
            }
            $addr = $this->addr;
            $port = $this->port;
        } else {
            if ($timeout === null) {
                $timeout = 0;
            }
            if ($addr === null || $port === null) {
                throw new \InvalidArgumentException('Both address and port must be specified!');
            }
        }

        if (!$this->isBlocking() && $timeout !== 0) {
            throw new \InvalidArgumentException('Timeout isn\'t compatible with non-blocking socket.');
        }

        if ($timeout === 0) {
            if (@socket_connect($this->socket, $addr->getHostAddress(), $port) === false) {
                $this->throwSocketError();
            }
        } else {
            $this->setBlocking(false);
            try {
                if (@socket_connect($this->socket, $addr->getHostAddress(), $port) === false) {
                    if (socket_last_error() !== SOCKET_EINPROGRESS) {
                        $this->throwSocketError();
                    }

                    if ($this->selectWrite($timeout) === false) {
                        throw new SocketException('Connection timeout', SOCKET_ETIMEDOUT);
                    }

                    if ($this->getOption(SOL_SOCKET, SO_ERROR) !== 0) {
                        throw new SocketException('Connection refused', SOCKET_ECONNREFUSED);
                    }
                }
            }
            finally {
                $this->setBlocking(true);
            }
        }

        $this->addr = $addr;
        $this->port = $port;
    }

    /**
     * Listens for a connection on this socket.
     * @link http://php.net/manual/en/function.socket-listen.php
     *
     * @param int $backlog a maximum of backlog incoming connections will be queued for processing.
     *
     * @throws SocketException
     */
    public function listen($backlog = 0)
    {
        if (@socket_listen($this->socket, $backlog) === false) {
            $this->throwSocketError();
        }
    }

    /**
     * Accepts a connection on this socket.
     * @link http://php.net/manual/en/function.socket-accept.php
     *
     * @return Socket
     *
     * @throws SocketException
     */
    public function accept()
    {
        $res = @socket_accept($this->socket);
        if ($res === false) {
            $this->throwSocketError();
        }

        return new Socket($res);
    }

    private function parseTimeout($msTimeout)
    {
        $sec = floor($msTimeout / 1000);
        $usec = ($msTimeout - ($sec * 1000)) * 1000;

        return ['sec' => $sec, 'usec' => $usec];
    }

    private function unparseTimeout($timeout)
    {
        return ($timeout['sec'] * 1000) + ($timeout['usec'] / 1000);
    }

    private function select($type, $timeout)
    {
        $timeout = $this->parseTimeout($timeout);

        $arr = [$this->socket];
        switch ($type) {
            case 'r':
                $ret = @socket_select($arr, $void, $void, $timeout['sec'], $timeout['usec']);
                break;
            case 'w':
                $ret = @socket_select($void, $arr, $void, $timeout['sec'], $timeout['usec']);
                break;
            case 'e':
                $ret = @socket_select($void, $void, $arr, $timeout['sec'], $timeout['usec']);
                break;
            default:
                throw new \InvalidArgumentException($type);
        }
        if ($ret === false) {
            $this->throwSocketError();
        }

        return !!$ret;
    }

    /**
     * Watch this socket to see if data become available for reading.
     *
     * @param int $timeout timeout in milliseconds
     *
     * @return bool true if data is available; else false when timeout
     *
     * @throws IOException
     */
    public function selectRead($timeout)
    {
        return $this->select('r', $timeout);
    }

    /**
     * Watch this socket to see if data become available for writing.
     *
     * @param int $timeout timeout in milliseconds
     *
     * @return bool true if data is available; else false when timeout
     *
     * @throws IOException
     */
    public function selectWrite($timeout)
    {
        return $this->select('w', $timeout);
    }

    /**
     * Watch this socket and wait for exception.
     *
     * @param int $timeout timeout in milliseconds
     *
     * @return bool true if exception is available; else false when timeout
     *
     * @throws IOException
     */
    public function selectException($timeout)
    {
        return $this->select('e', $timeout);
    }

    /**
     * In PHP, the management of timeouts can be more complicated than in languagues more appropriate to network IO.
     * The overall timeout defined how much time is given to future operations of reading and writing. Each read/wrire
     * call will reduce the global timeout value. If it reach 0, an exception will be generated.
     *
     * Don't use with non-blocking socket.
     *
     * @param int $timeout timeout in millis; null to remove
     */
    public function setGlobalTimeout($timeout)
    {
        $this->globalTimeout = $timeout;
    }

    /**
     * Returns the current global timeout value. Or null if there are none.
     *
     * @return int the current global timeout value
     */
    public function getGlobalTimeout()
    {
        return $this->globalTimeout;
    }

    /**
     * Set the timeout in milliseconds for each read call.
     *
     * @param int $timeout timeout in milliseconds
     */
    public function setReadTimeout($timeout)
    {
        $this->setOption(SOL_SOCKET, SO_RCVTIMEO, $this->parseTimeout($timeout));
    }

    /**
     * Get the read timeout.
     *
     * @return int timeout in milliseconds
     */
    public function getReadTimeout()
    {
        return $this->unparseTimeout($this->getOption(SOL_SOCKET, SO_RCVTIMEO));
    }

    /**
     * Set the timeout in milliseconds for each write call.
     *
     * @param int $timeout timeout in milliseconds
     */
    public function setWriteTimeout($timeout)
    {
        $this->setOption(SOL_SOCKET, SO_SNDTIMEO, $this->parseTimeout($timeout));
    }

    /**
     * Get the write timeout.
     *
     * @return int timeout in milliseconds
     */
    public function getWriteTimeout()
    {
        return $this->unparseTimeout($this->getOption(SOL_SOCKET, SO_SNDTIMEO));
    }

    /**
     * @return InputStream
     */
    public function getInputStream()
    {
        return $this->in;
    }

    /**
     * @return OutputStream
     */
    public function getOutputStream()
    {
        return $this->out;
    }

    /**
     * Gets socket options for this socket.
     * @link http://php.net/manual/en/function.socket-get-option.php
     *
     * @param int $level   the protocol level at which the option resides
     * @param int $optname a socket option
     *
     * @return mixed the option value
     * @throws SocketException
     */
    public function getOption($level, $optname)
    {
        $option = @socket_get_option($this->socket, $level, $optname);
        if ($option === false) {
            $this->throwSocketError();
        }

        return $option;
    }

    /**
     * Sets socket options for this socket.
     * @link http://php.net/manual/en/function.socket-set-option.php
     *
     * @param int   $level   the protocol level at which the option resides
     * @param int   $optname a socket option
     * @param mixed $value   the option value
     *
     * @throws SocketException
     */
    public function setOption($level, $optname, $value)
    {
        if (@socket_set_option($this->socket, $level, $optname, $value) === false) {
            $this->throwSocketError();
        }
    }

    /**
     * Sets blocking mode on a socket resource
     *
     * Blocking: When an operation (e.g. receive, send, connect, accept, ...) is performed on a blocking socket, the
     * script will pause its execution until it receives a signal or it can perform the operation.
     *
     * Non-blocking: When an operation (e.g. receive, send, connect, accept, ...) is performed on a non-blocking
     * socket, the script will not pause its execution until it receives a signal or it can perform the operation.
     * Rather, if the operation would result in a block, the called function will fail.
     * Exception SocketTryAgain will be thrown when a blocking operation is called.
     * @link http://php.net/manual/en/function.socket-set-nonblock.php
     *
     * @param bool $blocking blocking mode
     *
     * @throws SocketException
     */
    public function setBlocking($blocking)
    {
        if ($this->blocking == $blocking) {
            return;
        }

        if ($blocking) {
            if (@socket_set_block($this->socket) === false) {
                $this->throwSocketError();
            }
            $this->blocking = true;
        } else {
            if (@socket_set_nonblock($this->socket) === false) {
                $this->throwSocketError();
            }
            $this->blocking = false;
        }
    }

    /**
     * Returns the blocking state of the socket.
     *
     * @return bool true if the socket is blocking; else false
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * Places the input stream for this socket at "end of stream".
     * @link http://php.net/manual/en/function.socket-shutdown.php
     *
     * @throws SocketException
     */
    public function shutdownInput()
    {
        if (@socket_shutdown($this->socket, 0) === false) {
            $this->throwSocketError();
        }
        $this->in = null;
    }

    /**
     * Returns whether the read-half of the socket connection is closed.
     *
     * @return bool true if the input of the socket has been shutdown
     */
    public function isInputShutdown()
    {
        return ($this->in === null);
    }

    /**
     * Disables the output stream for this socket.
     * @link http://php.net/manual/en/function.socket-shutdown.php
     *
     * @throws SocketException
     */
    public function shutdownOutput()
    {
        if (@socket_shutdown($this->socket, 1) === false) {
            $this->throwSocketError();
        }
        $this->out = null;
    }

    /**
     * Returns whether the write-half of the socket connection is closed.
     *
     * @return bool true if the output of the socket has been shutdown
     */
    public function isOutputShutdown()
    {
        return ($this->out === null);
    }

    /**
     * Returns the closed state of the socket.
     *
     * @return bool true if the socket has been closed
     */
    public function isClosed()
    {
        return ($this->socket === null);
    }

    /**
     * Closes this socket.
     */
    public function close()
    {
        if (!$this->isClosed()) {
            @socket_close($this->socket);
            $this->socket = null;
        }
    }

}