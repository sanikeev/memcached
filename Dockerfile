FROM php:7.2-cli
RUN apt update && \
    apt install -y memcached && \
    docker-php-ext-install sockets
COPY . /usr/src/memcached
WORKDIR /usr/src/memcached
CMD service memcached start && vendor/bin/phpunit --colors --bootstrap=vendor/autoload.php tests/
