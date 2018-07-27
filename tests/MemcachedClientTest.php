<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:13
 */

namespace Test;

use Sanikeev\Memcached\Client;

class MemcachedClientTest extends \PHPUnit\Framework\TestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = new Client(['host' => 'localhost', 'port' => 11211]);
    }
    
    public function testCreateConnection()
    {
        $result = new Client(['host' => 'localhost', 'port' => 11211]);
        $this->assertTrue((bool) $result, "Error with connection");
    }

    public function testCreateAsyncConnection()
    {
        $result = new Client(['host' => 'localhost', 'port' => 11211, 'async' => true ]);
        $this->assertTrue((bool) $result, "Error with connection");
    }

    public function testSetVar()
    {
        $status = $this->client->set("testKey", "testVal", 60);
        $this->assertTrue($status, "Error setting data");
    }

    public function testGetVar()
    {
        $data = $this->client->get("testKey");
        $this->assertEquals("testVal", $data);
    }

    public function testDeleteVar()
    {
        $status = $this->client->delete("testKey");
        $this->assertTrue($status, "Error with deleting key");
    }

    public function testSend()
    {
        $val = '123';
        $expires = 5;
        $key = 'testKey';
        $data = serialize($val);
        $payload = sprintf("set %s 0 %d %d\r\n%s\r\n", $key, $expires, mb_strlen($data), $data);

        $reflection = new \ReflectionClass(get_class($this->client));
        $method = $reflection->getMethod('send');
        $method->setAccessible(true);

        $response = $method->invokeArgs($this->client, [$payload]);
        $this->assertEquals(trim($response), Client::RESPONSE_STORED, "Error with sending payload");
    }

    public function testCheckEndSignal()
    {
        $reflection = new \ReflectionClass(get_class($this->client));
        $method = $reflection->getMethod('isEnd');
        $method->setAccessible(true);

        $str = "END";
        $result = $method->invokeArgs($this->client, [$str]);
        $this->assertTrue($result, "Error checking end signal");
    }
}
