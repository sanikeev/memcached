<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:19
 */

namespace Sanikeev\Memcached;

use Sanikeev\Memcached\Exception\ConnectionException;
use Sanikeev\Memcached\Exception\HostNotSetException;
use Sanikeev\Memcached\Exception\PortNotSetException;
use Sanikeev\Memcached\Exception\SetDataException;

class Client implements ClientInterface
{

    protected $socket;

    const RESPONSE_ERROR = 'ERROR';
    const RESPONSE_STORED = 'STORED';
    const RESPONSE_DELETED = 'DELETED';
    const RESPONSE_END = 'END';

    protected $endingSignals = [
        self::RESPONSE_STORED,
        self::RESPONSE_DELETED,
        self::RESPONSE_ERROR,
        self::RESPONSE_END
    ];

    /**
     * Client constructor.
     * @param $options
     *  Сюда можно передать следующие опции
     *      $host - адрес хоста
     *      $port - порт хоста
     *      $async - не обязательно, включает неблокирующий режим
     * @throws ConnectionException
     */
    public function __construct($options)
    {
        if(!isset($options['host'])) {
            throw new HostNotSetException();
        }
        if (!isset($options['port'])) {
            throw new PortNotSetException();
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!socket_connect($socket,$options['host'], $options['port'])) {
            throw new ConnectionException();
        }
        if (isset($options['async']) && $options['async'] == true) {
            socket_set_nonblock($socket);
        }
        $this->socket = $socket;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $val, $expires = 0)
    {
        $data = serialize($val);
        $payload = sprintf("set %s 0 %d %d\r\n%s\r\n", $key, $expires, mb_strlen($data), $data);
        $response = $this->send($payload);
        if (trim($response) == self::RESPONSE_STORED) {
            return true;
        }
        if (trim($response) == self::RESPONSE_ERROR) {
            throw new SetDataException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $payload = sprintf("get %s\r\n", $key);
        $response = $this->send($payload);
        $regExp = sprintf("#VALUE\s%s\s\d+\s\d+\s+(.*?)\s+END\s+#is", $key);
        $match = [];
        if (preg_match($regExp, $response, $match)) {
            $data = unserialize($match[1]);
            return $data;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $payload = sprintf("delete %s\r\n", $key);
        $response = $this->send($payload);
        if (trim($response) == self::RESPONSE_DELETED) {
            return true;
        }

        return false;
    }

    /**
     * Отправляет данные на сокет и принимает ответ
     * @param string $payload
     * @return string
     */
    public function send($payload)
    {
        socket_write($this->socket, $payload);
        $response = '';
        do {
            $buffer = socket_read($this->socket,2048);
            $response .= $buffer;
            $condition = $buffer != "" || $buffer !== false;
            if ($this->isEnd($buffer)) {
                break;
            }
        } while ($condition);
        return $response;
    }

    /**
     * Проверяет окончание команды
     * @param $str
     * @return bool
     */
    public function isEnd($str) {
        foreach ($this->endingSignals as $end) {
            if(preg_match("#{$end}#imu", $str)) {
                return true;
            }
        }

        return false;
    }
}
