<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.07.18
 * Time: 0:48
 */

namespace Sanikeev\Memcached\Exception;


class PortNotSetException extends \Exception
{

    public function setMessage()
    {
        $this->message = 'Параметр $port не установлен';
    }
}