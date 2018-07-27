##Простой клиент для memcached

[![Build Status](https://travis-ci.org/sanikeev/memcached.svg?branch=master)](https://travis-ci.org/sanikeev/memcached)

Нативная реализация основных команд get, set, delete для memcached на php

### Установка

* Клонируйте этот репозиторий
* Выполните ``` composer install ```
* Затем выполните ` docker build -t memcached-php .`
* Для запуска тестов выполните ``` sudo docker run -it --rm --name memcached-php-1 memcached-php```

### Примеры использования

Синхронный режим

```php
<?php
    $client = new Sanikeev\Memcached\Client([
        'host' => 'localhost',
        'port' => 11211
    ]);
    
    // записывает значение в memcached на 60 секунд
    // если значение не задано то будет храниться бесконечно
    $client->set('key', 'value', 60);
    
    // получает сохраненное значение
    $data = $client->get('key');
    var_dump($data);
    
    // удаляет сохраненное значение
    $client->delete('key');
```

Асинхронный режим

```php
<?php
    $client = new \Sanikeev\Memcached\ClientAsync(['host' => 'localhost', 'port' => 11211]);
    $arrData = [
        'a' => 1,
        'b' => 2,
        'c' => 3
    ];

    // запись данных
    $request = [];
    foreach ($arrData as $key => $val) {
       $request[] = $client->request(\Sanikeev\Memcached\ClientAsync::SET_COMMAND, [
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
    var_dump($result);

    // получение данных
    $request = [];
    foreach ($arrData as $key => $val) {
        $request[] = $client->request(\Sanikeev\Memcached\ClientAsync::GET_COMMAND, [
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
    var_dump($result);
    
    
```