#Простой клиент для memcached

Нативная реализация основных команд get, set, delete для memcached на php

## Установка

* Клонируйте этот репозиторий
* Выполните ``` composer install ```
* Для запуска тестов выполните ``` vendor/bin/phpunit --colors --bootstrap=vendor/autoload.php tests/```

## Примеры использования

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
    $client = new Sanikeev\Memcached\Client([
        'host' => 'localhost',
        'port' => 11211,
        'async' => true
    ]);
    
    // далее вызовы идут как обычно
    
```