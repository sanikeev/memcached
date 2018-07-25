<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.18
 * Time: 11:49
 */

namespace Sanikeev\Memcached\Exception;

class SetDataException extends \Exception
{

    /**
     * @param mixed $message
     */
    public function setMessage()
    {
        $this->message = "Ошибка запсиси в memcached";
    }
}
