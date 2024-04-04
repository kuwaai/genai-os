FROM nginx:stable-alpine

WORKDIR /app
RUN rm -rf /etc/nginx/templates
COPY docker/web/templates /etc/nginx/templates
COPY --from=kuwa-multi-chat:latest /app/public /app/public

RUN apk --no-cache add curl