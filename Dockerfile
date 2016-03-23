FROM debian:jessie

MAINTAINER Bartomeu Miro as "speedcard@rtom.eu"

COPY slash /
COPY src /var/www/speedcard.gotes.org

RUN apt-get update
RUN apt-get install -y nginx php5-cli php5-fpm

# Some useful things
RUN apt-get install -y vim

RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log

RUN ln -sf /dev/stdout /var/log/php5-fpm.log

EXPOSE 80

CMD ["/usr/local/sbin/docker-startup.sh"]
