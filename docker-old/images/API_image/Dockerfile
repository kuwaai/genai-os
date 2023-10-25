FROM nvidia/cuda:12.1.1-runtime-ubuntu22.04

# Set working directory
WORKDIR /

# Copy any setup files or scripts into the image
COPY setup.sh /setup.sh
COPY requirements.txt /requirements.txt

# Run setup commands
RUN chmod +x /setup.sh && /setup.sh

ENTRYPOINT ["/bin/bash", "-c", "chown -R root:root API && chmod +x /API/run.sh && exec /API/run.sh"]
