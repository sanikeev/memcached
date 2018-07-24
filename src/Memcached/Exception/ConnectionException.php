<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.18
 * Time: 0:04
 */

namespace Sanikeev\Memcached\Exception;


class ConnectionException extends \Exception
{
    /**
     * @return mixed
     */
    public function setMessage()
    {
        return "Ошибка соединения с свервером Memcached";
    }
}