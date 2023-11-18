FROM php:7.0-apache

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY index.php index.php
COPY mysqli_fix.php mysqli_fix.php
COPY assets assets

