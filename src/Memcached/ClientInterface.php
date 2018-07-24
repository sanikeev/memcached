<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:58
 */

namespace Sanikeev\Memcached;


interface ClientInterface
{
    public function connect($host = 'localhost', $port = 11211, $async = false);

    public function set($key, $value, $expires);

    public function get($key);

    public function delete($key);

    public function close();

}