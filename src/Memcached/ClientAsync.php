<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.07.18
 * Time: 17:16
 */

namespace Sanikeev\Memcached;

use function Couchbase\defaultDecoder;
use Sanikeev\Memcached\Exception\ConnectionException;
use Sanikeev\Memcached\Exception\MethodNotFoundException;

class ClientAsync
{
    protected $requestStack = [];
    protected $host;
    protected $port;
    protected $endingSignals = [
        self::RESPONSE_STORED,
        self::RESPONSE_DELETED,
        self::RESPONSE_ERROR,
        self::RESPONSE_END
    ];

    const RESPONSE_ERROR = 'ERROR';
    const RESPONSE_STORED = 'STORED';
    const RESPONSE_DELETED = 'DELETED';
    const RESPONSE_END = 'END';

    const GET_COMMAND = 'get';
    const SET_COMMAND = 'set';
    const DELETE_COMMAND = 'delete';

    public function __construct($options)
    {
        $this->host = $options['host'];
        $this->port = $options['port'];
    }

    /**
     * @param $method
     * @param $options
     * @return string
     * @throws ConnectionException
     * @throws MethodNotFoundException
     */
    public function request($method, $options)
    {
        $connectionKey = md5(sprintf('%s%s', $method, var_export($options, true)));
        $connection = $this->connect();
        switch ($method) {
            case self::SET_COMMAND:
                $expires = isset($options['expires']) ? $options['expires'] : 0;
                $data = serialize($options['data']);
                $payload = \sprintf("set %s 0 %d %d\r\n%s\r\n", $options['key'], $expires, mb_strlen($data), $data);
                break;
            case self::GET_COMMAND:
                $payload = \sprintf("get %s\r\n", $options['key']);
                break;
            case self::DELETE_COMMAND:
                $payload = \sprintf("delete %s\r\n", $options['key']);
                break;
            default:
                throw new MethodNotFoundException();
        }
        socket_write($connection, $payload);
        $this->requestStack[$connectionKey] = $connection;
        return $connectionKey;
    }

    /**
     * @param $request
     * @param $callback
     */
    public function response($request, $callback)
    {
        $connection = $this->requestStack[$request];
        $response = '';
        do {
            $buffer = \socket_read($connection, 2048);
            $response .= $buffer;
            $regExp ="#VALUE\s\S+\s\d+\s\d+\s+(.*?)\s+END\s+#is";
            $match = [];
            if (\preg_match($regExp, $response, $match)) {
                $data = \unserialize($match[1]);
                $callback($data);
                break;
            }
            $condition = $buffer != "" || $buffer !== false;
            if ($this->isEnd($buffer)) {
                $callback(trim($buffer));
                break;
            }
        } while ($condition);
    }

    /**
     * @return resource
     * @throws ConnectionException
     */
    private function connect()
    {
        $socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!\socket_connect($socket, $this->host, $this->port)) {
            throw new ConnectionException();
        }
        \socket_set_nonblock($socket);
        return $socket;
    }

    /**
     * Проверяет окончание команды
     * @param $str
     * @return bool
     */
    private function isEnd($str)
    {
        foreach ($this->endingSignals as $end) {
            if (\preg_match("#{$end}#imu", $str)) {
                return true;
            }
        }

        return false;
    }
}
