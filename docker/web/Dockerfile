FROM nginx:stable-alpine

RUN apk --no-cache add curl
RUN mkdir -p /var/log/php-fpm

WORKDIR /app
RUN rm -rf /etc/nginx/templates
COPY docker/web/templates /etc/nginx/templates

COPY --from=kuwa-multi-chat:latest /app/public /app/public
RUN chown -R nginx:nginx /app