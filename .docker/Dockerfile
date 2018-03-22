FROM php:7
MAINTAINER adam.stipak@gmail.com

# system deps
RUN apt-get update && \
  apt-get install -y \
    git \
    gnupg

# system deps configuration
RUN curl -sL https://deb.nodesource.com/setup_7.x | bash -

# nodejs
RUN apt-get install -y \
    zlib1g-dev \
    nodejs

# php extensions
RUN docker-php-ext-install zip

# binaries
RUN curl https://getcomposer.org/composer.phar -o "/usr/local/bin/composer" && \
  chmod +x /usr/local/bin/composer

# gulp dependencies
RUN npm install -g \
  gulp \
  gulp-watch

WORKDIR /src
CMD composer install && \
  npm install && \
  gulp
