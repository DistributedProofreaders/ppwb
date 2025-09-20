#!/bin/sh

# Create working directory
mkdir -p /var/www/html/t

# Rebuild pptext binary
cd /var/www/html/bin/pptext
go build pptext.go

# Configure path to Python venv
echo '<?php $python_runner = "/venv/bin/python3"; ?>' > /var/www/html/config.php

# Call the original entrypoint (plus CMD)
cd
exec docker-php-entrypoint $@
