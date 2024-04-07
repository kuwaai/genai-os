# Installation for Docker

## Software versions
- Docker Compose V2+
- Docker Engine 18.06.0+
- Docker Engine tested version:
  - 25.0.3 (git commit: f417435)
  - 25.0.4 (git commit: 061aa95)

## Environment installation
The following instructions have been tested on Ubuntu 22.04 LTS, if you are using a different Linux distribution, please refer to the relevant documentation.  
If you need to use GPU for model inference, please install CUDA and NVIDIA Container Toolkit.

### 1. (Optional) Install CUDA Driver

Refer to the documentation: [NVIDIA CUDA Installation Guide for Linux](https://docs.nvidia.com/cuda/cuda-installation-guide-linux/)

```sh
# Install the header of current running kernel
sudo apt install -y linux-headers-$(uname -r)
# or for auto-upgrade
sudo apt install -y linux-headers-generic

# Install the keyring
distribution=$(. /etc/os-release;echo $ID$VERSION_ID | sed -e 's/\.//g')
wget https://developer.download.nvidia.com/compute/cuda/repos/$distribution/x86_64/cuda-keyring_1.1-1_all.deb
sudo dpkg -i cuda-keyring_1.1-1_all.deb
rm cuda-keyring_1.1-1_all.deb
sudo apt update

# Install the NVIDIA driver without any X Window packages
sudo apt install -y --no-install-recommends cuda-drivers
sudo reboot

# Verify the version of installed driver
cat /proc/driver/nvidia/version
# Output sample:
# NVRM version: NVIDIA UNIX x86_64 Kernel Module  545.23.08  Mon Nov  6 23:49:37 UTC 2023
# GCC version:  gcc version 11.4.0 (Ubuntu 11.4.0-1ubuntu1~22.04)
```

### 2. Install Docker and Docker Compose

Refer to the documentation: [Install Docker Engine on Ubuntu](https://docs.docker.com/engine/install/ubuntu/)
```sh
# Add official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Setup repository
echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update

# Install necessary package
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Enable the service
sudo systemctl --now enable docker

# Enable unattended-update
cat << EOT | sudo tee /etc/apt/apt.conf.d/51unattended-upgrades-docker
Unattended-Upgrade::Origins-Pattern {
    "origin=Docker";
};
EOT
```

### 3. (Optional) Install NVIDIA Container Toolkit

Refer to the documentation: [Installing the NVIDIA Container Toolkit](https://docs.nvidia.com/datacenter/cloud-native/container-toolkit/latest/install-guide.html)

```sh
# Setup GPG key
curl -fsSL https://nvidia.github.io/libnvidia-container/gpgkey | sudo gpg --dearmor -o /usr/share/keyrings/nvidia-container-toolkit-keyring.gpg

# Setup the repository
distribution=$(. /etc/os-release;echo $ID$VERSION_ID)
curl -s -L https://nvidia.github.io/libnvidia-container/$distribution/libnvidia-container.list | \
  sed 's#deb https://#deb [signed-by=/usr/share/keyrings/nvidia-container-toolkit-keyring.gpg] https://#g' | \
  sudo tee /etc/apt/sources.list.d/nvidia-container-toolkit.list
sudo apt-get update
sudo apt-get install -y nvidia-container-toolkit

# Configure the NVIDIA runtime to be the default docker runtime
sudo nvidia-ctk runtime configure --runtime=docker --set-as-default
sudo systemctl restart docker

# Verify
sudo docker pull nvidia/cuda:12.2.0-base-ubuntu22.04
sudo docker run --rm --gpus all nvidia/cuda:12.2.0-base-ubuntu22.04 nvidia-smi
```

## Basic installation

### 1. Change configuration files

Copy `.admin-password.sample`, `.db-password.sample`, `.env.sample` and remove the `.sample` suffix.  
The files are as follows:
- `.admin-password`: Default administrator password, it is recommended not to keep the default value
- `.db-password`: System built-in database password, it is recommended to set it to a random string of sufficient length
- `.env`: Other system environment variables, the minimum set value is as follows
    ```sh
    DOMAIN_NAME=localhost # Website domain name, if you want to make the service public, please set it to your public domain name
    PUBLIC_BASE_URL="http://${DOMAIN_NAME}/" # Website base URL

    ADMIN_NAME="Kuwa Admin" # Website default administrator name
    ADMIN_EMAIL="admin@${DOMAIN_NAME}" # Website default administrator login email, which can be an invalid email
    ```

### 2. Start the system

> [!WARNING]
> Please use Docker Compose V2 or above.
> The `docker-compose` package in Ubuntu APT is Docker Compose V1, which cannot be used. Please refer to the previous section to install the new version of Docker Compose

Start the basic Kuwa GenAI OS system, PostgreSQL and Gemini-Pro Executor using Docker Compose
```sh
docker compose -f compose.yaml -f pgsql.yaml -f gemini.yaml up --build
```