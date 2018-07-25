<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.07.18
 * Time: 0:50
 */

namespace Sanikeev\Memcached\Exception;


class HostNotSetException extends \Exception
{

    public function setMessage()
    {
        $this->message = 'Параметр $host не установлен';
    }
}