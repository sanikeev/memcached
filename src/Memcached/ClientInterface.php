<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:58
 */

namespace Sanikeev\Memcached;

use Sanikeev\Memcached\Exception\SetDataException;

interface ClientInterface
{
    /**
     * @param $key
     * @param $value
     * @param $expires
     * @return bool
     * @throws SetDataException
     */
    public function set($key, $value, $expires);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function delete($key);
}
