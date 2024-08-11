#!/bin/bash

REBOOT_FLAG=".reboot_flag"
NOW_PATH=$(pwd)

install_docker() {
        # Add official GPG key
        if ! command -v docker &> /dev/null; then
                echo "Installing Docker..."

                # Uninstall conflicting packages
                for pkg in docker.io docker-doc docker-compose docker-compose-v2 podman-docker containerd runc; do sudo apt-get remove $pkg; done

                # Add docker's official GPG key
                apt-get update
                apt-get install ca-certificates
                install -m 0755 -d /etc/apt/keyrings
                curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
                chmod a+r /etc/apt/keyrings/docker.asc

                # Setup repository
                echo \
                        "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
                        "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
                        tee /etc/apt/sources.list.d/docker.list > /dev/null
                apt-get update

                # Install necessary package
                apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

                # Enable the service
                systemctl --now enable docker

                # Enable unattended-update
                cat <<EOT | tee /etc/apt/apt.conf.d/51unattended-upgrades-docker
                Unattended-Upgrade::Origins-Pattern {
                        "origin=Docker";
                };
EOT
        else
                echo "Docker is already installed."
        fi
}

install_nvidia_driver() {
        if ! command -v nvidia-smi &> /dev/null; then
                echo "Installing Nvidia driver..."
                apt update
                apt upgrade -y

                # Remove previous NVIDIA installation
                apt autoremove nvidia* --purge -y
                apt autoclean

                # Install Ubuntu and NVIDIA drivers
                local version=$(ubuntu-drivers devices 2>/dev/null | grep recommended | grep -oP 'nvidia-driver-\d+')
                #echo "$version"
                ubuntu-drivers autoinstall
                apt install -y $version

                # Reboot
                touch "$REBOOT_FLAG"
                reboot
        else
                echo "Nvidia Driver is already installed."
        fi

}

install_cuda_toolkit() {
        if ! command -v nvcc --version &> /dev/null; then
                echo "Installing CUDA toolkit..."
                apt update
                apt upgrade -y

                # Install CUDA toolkit
                apt install -y nvidia-cuda-toolkit
        else
                echo "CUDA toolkit is already installed."
        fi
}

install_nvidia_container_toolkit() {
        if ! command -v which nvidia-container-toolkit &> /dev/null; then
                echo "Installing Nvidia continaer toolkit"

                # Setup GPG key
                curl -fsSL https://nvidia.github.io/libnvidia-container/gpgkey | gpg --dearmor -o /usr/share/keyrings/nvidia-container-toolkit-keyring.gpg

                # Setup the repository
                local distribution=$(. /etc/os-release;echo $ID$VERSION_ID)
                curl -s -L https://nvidia.github.io/libnvidia-container/$distribution/libnvidia-container.list | \
                        sed 's#deb https://#deb [signed-by=/usr/share/keyrings/nvidia-container-toolkit-keyring.gpg] https://#g' | \
                        tee /etc/apt/sources.list.d/nvidia-container-toolkit.list
                apt-get update
                apt-get install -y nvidia-container-toolkit

                # Configure the NVIDIA runtime to be the default docker runtime
                nvidia-ctk runtime configure --runtime=docker --set-as-default
                systemctl restart docker
        else
                echo "Nvidia toolkit is already installed."
        fi
}

install_kuwa() {
        # Download Kuwa repository
        cd "$NOW_PATH"
        git clone https://github.com/kuwaai/genai-os/
        cd genai-os/docker

        # Change configuration files
        cp .admin-password.sample .admin-password
        cp .db-password.sample .db-password
        cp .env.sample .env

        # Set admin password
        while true; do
                read -sp "Enter admin password: " admin_passwd
                echo
                read -sp "Confirm admin password: " admin_passwd_confirm
                echo

                if [ "$admin_passwd" == "$admin_passwd_confirm" ]; then
                        echo "Admin password set successfully."
                        echo "$admin_passwd" > .admin-password
                        break
                else
                        echo "Passwords do not match. Please enter your password again."
                fi
        done

        # Set database password
        while true; do
                read -sp "Enter database password: " db_passwd
                echo
                read -sp "Confirm database password: " db_passwd_confirm
                echo

                if [ "$db_passwd" == "$db_passwd_confirm" ]; then
                        echo "Database password set successfully."
                        echo "$db_passwd" > .db-password
                        break
                else
                        echo "Passwords do not match. Please enter your password again."
                fi
        done
}

fix() {
        cd executor
        echo "llama-cpp-python @ https://github.com/abetlen/llama-cpp-python/releases/download/v0.2.77/llama_cpp_python-0.2.77-cp310-cp310-linux_x86_64.whl" > requirements.txt
        cd ../../src/executor
        sed -i 's/==/ == /g' requirements.txt
        cd ../../docker/executor
        local LINES_TO_ADD=$(cat << 'EOF'
        COPY docker/executor/requirements.txt ./requirements-docker.txt
EOF
        )
        sed -i "8a${LINES_TO_ADD}" "Dockerfile"
        LINES_TO_ADD=$(cat << 'EOF'
        RUN pip install --no-cache-dir -r requirements-docker.txt
EOF
        )
        sed -i "9a${LINES_TO_ADD}" "Dockerfile"
        cd ..
}

install_all() {
        if [[ -f "$REBOOT_FLAG" ]]; then
                echo "System has been rebooted. Continuing installation."
                install_cuda_toolkit
                install_nvidia_container_toolkit
                rm -f "$REBOOT_FLAG"
        elif ! command -v nvidia-smi &> /dev/null; then
                apt-get update
                apt-get upgrade
        else
                apt-get install curl
                read -p "Do you want to install NVIDIA GPU drivers? [y/N]: " install_gpu
                if [[ "$install_gpu" == "y" || "$install_gpu" == "Y" ]]; then
                        install_nvidia_driver
                fi
        fi
        install_docker
        install_kuwa
        fix
        #cd "$PATH/genai-os/docker"
        cp run.sh.sample run.sh
        ./run.sh
}

echo "Kuwa docker build script v0.1.0"
echo "This will install kuwa automatically on Ubuntu 22.04"
sleep 2
install_all
