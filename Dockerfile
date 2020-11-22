FROM php:7.4-apache
COPY src/ /var/www/html/

ENV RESOURCEKEY=AQQNRUQFJWB2xxWO2Eg

# dependencies
RUN apt-get update \
  && DEBIAN_FRONTEND=noninteractive apt-get install --assume-yes --no-install-recommends \
  git unzip \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  && rm -rf /var/lib/apt/lists/*

# GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j "$(nproc)" gd

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --working-dir=/var/www require 51degrees/fiftyone.geolocation
# hack
RUN composer --working-dir=/var/www/vendor/51degrees/fiftyone.geolocation install

RUN mkdir /var/www/images
RUN chmod a+rwx /var/www/images
RUN mkdir /var/www/cache
RUN chmod a+rwx /var/www/cache
