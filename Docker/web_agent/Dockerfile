FROM ubuntu:22.04
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
# Set working directory
WORKDIR /

# Copy configurations and scripts into the image
COPY configs /configs
COPY scripts /scripts

# Run setup commands
RUN chmod +x /scripts/* && /scripts/setup_env.sh

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["/scripts/run.sh"]
