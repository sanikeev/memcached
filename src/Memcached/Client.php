<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:19
 */

namespace Sanikeev\Memcached;

class Client implements ClientInterface
{

    protected $socket;
    protected $isAsync;

    public function connect($host = 'localhost', $port = '11211', $async = false)
    {
        $err = null;
        $socket = fsockopen($host, $port, $err);
        if (!$socket || $err) {
            return false;
        }
        $this->socket = $socket;
        $this->isAsync = $async;
        return true;
    }

    public function set($key, $val, $expires = 0)
    {
        $data = serialize($val);
        $payload = sprintf("set %s 0 %d %d\r\n%s\r\n", $key, $expires, mb_strlen($data), $data);
        if ($this->isAsync) {
            $payload = sprintf("set %s 0 %d %d noreply\r\n%s\r\n", $key, $expires, mb_strlen($data), $data);
        }
        fwrite($this->socket, $payload);
        $response = fread($this->socket, 1024 * 100);
        if ($this->isAsync) {
            return true;
        }
        if (trim($response) == "STORED") {
            return true;
        }
        if (trim($response) == "ERROR") {
            return false;
        }
    }

    public function get($key)
    {
        $payload = sprintf("get %s\r\n", $key);
        fwrite($this->socket, $payload);
        $response = fread($this->socket, 1024 * 100);
        $regExp = sprintf("#VALUE\s%s\s\d+\s\d+\s+(.*?)\s+END\s+#is",$key);
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
        if ($this->isAsync) {
            $payload = sprintf("delete %s noreply\r\n", $key);
        }
        fwrite($this->socket, $payload);
        $response = fread($this->socket, 1024 * 100);
        if ($this->isAsync) {
            return true;
        }
        if (trim($response) == "DELETED") {
            return true;
        }

        return false;
    }

    public function close()
    {
        fclose($this->socket);
    }
}