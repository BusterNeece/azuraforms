FROM gitpod/workspace-full:latest

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer