FROM ubuntu:22.04
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
# Set working directory
WORKDIR /

# Copy any setup files or scripts into the image
COPY setup.sh /setup.sh
COPY .env /.env

# Run setup commands
RUN chmod +x /setup.sh && /setup.sh

CMD ["/bin/bash", "-c", "chown -R root:root script | chmod +x /script/run.sh | /script/run.sh"]
