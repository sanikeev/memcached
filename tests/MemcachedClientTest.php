<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.18
 * Time: 16:13
 */

namespace Test;

class MemcachedClientTest extends \PHPUnit\Framework\TestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = new \Sanikeev\Memcached\Client();
    }
    
    public function testCreateConnection()
    {
        $host = "localhost";
        $port = 11211;
        $result = $this->client->connect($host, $port);
        $this->assertTrue((bool) $result, "Error with connection");
        $this->client->close();
    }

    public function testSetVar()
    {
        $this->client->connect();
        $status = $this->client->set("testKey", "testVal", 60);
        $this->assertTrue($status, "Error setting data");
        $this->client->close();
    }

    public function testGetVar()
    {
        $this->client->connect();
        $data = $this->client->get("testKey");
        $this->assertEquals("testVal", $data);
        $this->client->close();
    }

    public function testDeleteVar()
    {
        $this->client->connect();
        $status = $this->client->delete("testKey");
        $this->assertTrue($status, "Error with deleting key");
        $this->client->close();
    }
}
