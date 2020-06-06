FROM nginx

ADD vhost.conf /etc/nginx/conf.d/default.conf
ADD server.key /etc/nginx/server.key
ADD server.crt /etc/nginx/server.crt

WORKDIR /app
RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /app
