FROM gitpod/workspace-full:latest

USER root

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER gitpod