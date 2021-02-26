FROM php:7.4-fpm

WORKDIR /var/www/web

#Run all commands from before_script that we only want to run once
RUN apt-get update -yqq
RUN apt-get install git -yqq
RUN apt-get install --yes zip unzip wget zlib1g-dev libzip-dev libxml2-dev libpng-dev libfreetype6-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype
RUN docker-php-ext-install gd pdo_mysql soap zip
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug gd

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# Install phpunit
RUN curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
RUN chmod +x /usr/local/bin/phpunit

# Install phpcs
RUN pear install PHP_CodeSniffer

# Install mysql client (`apt-cache search mysql-client` to see options)
RUN apt-get install -qqy --no-install-recommends default-mysql-client

# Install phinx
RUN composer global require robmorgan/phinx
RUN ln -s /root/.composer/vendor/robmorgan/phinx/bin/phinx /usr/bin/
