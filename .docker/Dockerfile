FROM php:7
MAINTAINER adam.stipak@gmail.com

# system deps
RUN apt-get update
RUN apt-get install -y git

# php extensions
RUN apt-get install zlib1g-dev && docker-php-ext-install zip

# binaries
RUN curl https://getcomposer.org/composer.phar -o "/usr/local/bin/composer" &&\
  chmod +x /usr/local/bin/composer

WORKDIR /src
CMD composer install &&\
  vendor/bin/statie generate
