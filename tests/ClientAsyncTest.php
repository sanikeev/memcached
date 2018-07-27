<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.07.18
 * Time: 17:18
 */

namespace Test;

use PHPUnit\Framework\TestCase;
use Sanikeev\Memcached\ClientAsync;

class ClientAsyncTest extends TestCase
{

    public function testSetDataAsync()
    {
        $client = new ClientAsync(['host' => 'localhost', 'port' => 11211]);
        $arrData = [
            'a' => 1,
            'b' => 2,
            'c' => 3
        ];

        $request = [];
        foreach ($arrData as $key => $val) {
            $request[] = $client->request(ClientAsync::SET_COMMAND, [
                'key' => $key,
                'data' => $val,
                'expires' => 100
            ]);
        }
        // do some long staff
        sleep(3);

        $result = [];
        foreach ($request as $item) {
            $client->response($item, function ($response) use (&$result) {
                $result[] = $response;
            });
        }
        foreach ($result as $value) {
            $this->assertEquals($value, ClientAsync::RESPONSE_STORED);
        }
    }

    public function testGetDataAsync()
    {
        $client = new ClientAsync(['host' => 'localhost', 'port' => 11211]);
        $arrData = [
            'a' => 1,
            'b' => 2,
            'c' => 3
        ];

        $request = [];
        foreach ($arrData as $key => $val) {
            $request[] = $client->request(ClientAsync::GET_COMMAND, [
                'key' => $key,
            ]);
        }
        // do some long staff
        sleep(3);

        $result = [];
        foreach ($request as $item) {
            $client->response($item, function ($response) use (&$result) {
                $result[] = $response;
            });
        }
        $arrDataVals = array_values($arrData);
        foreach ($result as $value) {
            $this->assertTrue(in_array($value, $arrDataVals));
        }
    }
}
