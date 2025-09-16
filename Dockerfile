FROM php:8.2.29-apache

# Did not find the aspell packages for Afrikaans, Latin
RUN apt-get update \
    && apt-get install -y \
        dwdiff python3-full golang libzip-dev aspell \
        aspell-en aspell-da aspell-nl aspell-eo aspell-fr \
        aspell-de aspell-el aspell-it aspell-pt aspell-ro \
        aspell-es aspell-gl-minimos \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN mkdir /venv \
    && python3 -m venv /venv \
    && /venv/bin/pip3 install \
        tinycss cssselect lxml html5lib pytest Pillow regex
RUN pecl install zip \
    && echo "extension=zip" >> $PHP_INI_DIR/php.ini-development \
    && mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY docker-entrypoint.sh /
RUN chmod 755 /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

# this CMD is in the upstream image but it gets lost somehow; redefine it
CMD ["apache2-foreground"]
