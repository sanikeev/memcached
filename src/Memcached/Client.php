<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:19
 */

namespace Sanikeev\Memcached;

use Sanikeev\Memcached\Exception\ConnectionException;

class Client implements ClientInterface
{

    protected $socket;

    const RESPONSE_ERROR = 'ERROR';
    const RESPONSE_STORED = 'STORED';
    const RESPONSE_DELETED = 'DELETED';

    public function __construct($options)
    {
        $err = null;
        $socket = fsockopen($options['host'], $options['port'], $err);
        if (!$socket || $err) {
            throw new ConnectionException();
        }
        $this->socket = $socket;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function __destruct()
    {
        if (!is_resource($this->socket)) {
            return ;
        }
        $this->close();
    }

    public function __sleep()
    {
        if (!is_resource($this->socket)) {
            return ;
        }
        $this->close();
    }

    public function set($key, $val, $expires = 0)
    {
        $data = serialize($val);
        $payload = sprintf("set %s 0 %d %d\r\n%s\r\n", $key, $expires, mb_strlen($data), $data);
        $response = $this->send($payload);
        if (trim($response) == self::RESPONSE_STORED) {
            return true;
        }
        if (trim($response) == self::RESPONSE_ERROR) {
            return false;
        }
    }

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

    public function delete($key)
    {
        $payload = sprintf("delete %s\r\n", $key);
        $response = $this->send($payload);
        if (trim($response) == self::RESPONSE_DELETED) {
            return true;
        }

        return false;
    }

    public function close()
    {
        fclose($this->socket);
    }

    public function send($payload)
    {
        fwrite($this->socket, $payload);
        $response = fread($this->socket, 1024 * 100);
        return $response;
    }
}
