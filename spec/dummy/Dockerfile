FROM prestashop/prestashop:latest

COPY init-scripts /tmp/init-scripts
COPY lemonink /lemonink

RUN apt-get update && apt-get install -y rsync

VOLUME [ "/var/www/html/modules/lemonink" ]