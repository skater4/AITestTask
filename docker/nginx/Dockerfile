FROM nginx:stable

RUN { \
    echo 'server {'; \
    echo '  listen 80 default_server;'; \
    echo '  return 444;'; \
    echo '}'; \
    } > /etc/nginx/conf.d/default.conf \
    && mkdir /etc/nginx/sites-enabled

COPY nginx.conf /etc/nginx
COPY vhost.conf /etc/nginx/sites-enabled
