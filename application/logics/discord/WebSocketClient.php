<?php
namespace Logics\Discord;

use Cores\Config\Config;

use React\EventLoop\StreamSelectLoop;
use React\Socket\Connection;
use Ratchet\WebSocket\Version\RFC6455\Frame;

/**
 * Class WebSocketClient
 */
class WebSocketClient
{
    const VERSION = '0.1.4';
    const TOKEN_LENGHT = 16;
    
    /** @var string $key */
    private $key;
    
    /** @var StreamSelectLoop $loop */
    private $loop;
    
    /** @var WebSocketClientInterface $client */
    private $client;
    
    /** @var string $host */
    private $host;
    
    /** @var int $port */
    private $port;
    
    /** @var string $path */
    private $path;
    
    /** @var Connection $socket */
    private $socket;
    
    /** @var bool $connected */
    private $connected = false;
    
    /** @var array $callbacks */
    private $callbacks = array();
    
    /**
     * @param WebSocketClientInterface $client
     * @param StreamSelectLoop $loop
     * @param string $host
     * @param int $port
     * @param string $path
     */
    function __construct(WebSocketClientInterface $client, StreamSelectLoop $loop, $host = '127.0.0.1', $port = 8080, $path = '/')
    {
        $this->setLoop($loop)->setHost($host)->setPort($port)->setPath($path)->setClient($client)->setKey($this->generateToken(self::TOKEN_LENGHT));
        
        $this->connect();
        $client->setClient($this);
    }
    
    /**
     * Disconnect on destruct
     */
    function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * Connect client to server
     *
     * @return self
     */
    public function connect()
    {
        $root = $this;
        $client = stream_socket_client("tls://{$this->getHost()}:{$this->getPort()}", $errno, $errstr);
//        $client = @fsockopen("ssl://{$this->getHost()}", $this->getPort(), $errno, $errstr, 5);

        if (!$client) {
            throw new \RuntimeException('Cannot connect to socket ([#' . $errno . '] ' . $errstr . ')');
        }

//        $this->setSocket(new Connection($client, $this->getLoop()));
        $this->setSocket($client);
        $this->getSocket()->on('data', function ($data) use ($root) {
            $data = $root->parseIncomingRaw($data);
            $root->parseData($data);
        });
        $this->getSocket()->write($this->createHeader());
        
        return $this;
    }
    
    /**
     * Disconnect from server
     */
    public function disconnect()
    {
        $this->connected = false;
        $this->socket->close();
    }
    
    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * @param $data
     * @param $header
     */
    public function receiveData($data, $header)
    {
        if (!$this->isConnected()) {
            $this->disconnect();
            return;
        }
        
        $this->client->onMessage($data);
    }
    
    /**
     * @param $data
     * @param string $type
     * @param bool $masked
     */
    public function sendData($data, $type = 'text', $masked = true)
    {
        if (!$this->isConnected()) {
            $this->disconnect();
            return;
        }
        
        $msg = new Frame(json_encode($data));
        
        $this->getSocket()->write($msg->getContents());
    }
    
    /**
     * Parse received data
     *
     * @param $response
     */
    public function parseData($response)
    {
        if (!$this->connected && isset($response['Sec-Websocket-Accept'])) {
            if (base64_encode(pack('H*', sha1($this->key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))) === $response['Sec-Websocket-Accept']) {
                $this->connected = true;
            }
        }
        
        if ($this->connected && !empty($response['content'])) {
            $content = trim($response['content']);
            $frame = new Frame();
            $frame->addBuffer($content);
            $content = $frame->getPayload();
            
            $content = utf8_encode($content);
            $data = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                unset($response['status']);
                unset($response['content']);
                $this->receiveData($data, $response);
            } else {
                echo 'JSON decode error [#' . json_last_error() . ']';
            }
            
            $overflow = $frame->extractOverflow();
            if ($overflow) {
                $this->parseData(array('content' => $overflow));
            }
        }
    }
    
    /**
     * Create header for websocket client
     *
     * @return string
     */
    public function createHeader()
    {
        $host = $this->getHost();
        if ($host === '127.0.0.1' || $host === '0.0.0.0') {
            $host = 'localhost';
        }
        
        return "GET {$this->getPath()} HTTP/1.1" . "\r\n" . "Origin: null" . "\r\n" . "Host: {$host}:{$this->getPort()}" . "\r\n" . "Sec-WebSocket-Key: {$this->getKey()}" . "\r\n" . "User-Agent: PHPWebSocketClient/" . self::VERSION . "\r\n" . "Upgrade: websocket" . "\r\n" . "Connection: Upgrade" . "\r\n" . "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }
    
    /**
     * Parse raw incoming data
     *
     * @param $header
     * @return array
     */
    public function parseIncomingRaw($header)
    {
        var_dump($header);
        $retval = array();
        $content = "";
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($matches) {
                    return strtoupper($matches[0]);
                }, strtolower(trim($match[1])));
                if (isset($retval[$match[1]])) {
                    $retval[$match[1]] = array($retval[$match[1]], $match[2]);
                } else {
                    $retval[$match[1]] = trim($match[2]);
                }
            } else {
                if (preg_match('!HTTP/1\.\d (\d)* .!', $field)) {
                    $retval["status"] = $field;
                } else {
                    $content .= $field . "\r\n";
                }
            }
        }
        $retval['content'] = $content;
        
        return $retval;
    }
    
    /**
     * Generate token
     *
     * @param int $length
     * @return string
     */
    public function generateToken($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        
        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);
        
        return base64_encode($randomString);
    }
    
    /**
     * Generate token
     *
     * @param int $length
     * @return string
     */
    public function generateAlphaNumToken($length)
    {
        $characters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        
        srand((float)microtime() * 1000000);
        
        $token = '';
        
        do {
            shuffle($characters);
            $token .= $characters[mt_rand(0, (count($characters) - 1))];
        } while (strlen($token) < $length);
        
        return $token;
    }
    
    /**
     * @param int $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * @param Connection $socket
     * @return self
     */
    public function setSocket(Connection $socket)
    {
        $this->socket = $socket;
        return $this;
    }
    
    /**
     * @return Connection
     */
    public function getSocket()
    {
        return $this->socket;
    }
    
    /**
     * @param string $host
     * @return self
     */
    public function setHost($host)
    {
        $this->host = (string)$host;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * @param string $key
     * @return self
     */
    public function setKey($key)
    {
        $this->key = (string)$key;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * @param WebSocketClientInterface $client
     * @return self
     */
    public function setClient(WebSocketClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }
    
    /**
     * @return WebSocketClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
    
    /**
     * @param StreamSelectLoop $loop
     * @return self
     */
    public function setLoop(StreamSelectLoop $loop)
    {
        $this->loop = $loop;
        return $this;
    }
    
    /**
     * @return StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }
    
}