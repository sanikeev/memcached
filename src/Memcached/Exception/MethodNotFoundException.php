<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.07.18
 * Time: 17:36
 */

namespace Sanikeev\Memcached\Exception;

class MethodNotFoundException extends \Exception
{
    /**
     * @param mixed $message
     */
    public function setMessage()
    {
        $this->message = 'Такого метода не существует';
    }
}
