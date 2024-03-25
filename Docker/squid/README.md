# Squid

- Docker image of Squid, the web caching server
- Currently, this service is used by the crawler to improve the performance
- The configuration is adopt from [Rasika Perera, "How I Saved Tons of GBs with HTTPs Caching"](https://rasika90.medium.com/how-i-saved-tons-of-gbs-with-https-caching-41550b4ada8a) with minor modification

## Build

1. Create the self-signed CA certification
    ```bash
    pushd ./cert
    ./gen_ca.sh # Wait for a while...
    popd # Back to this directory
    ```
2. Run the service
    ```bash
    sudo docker compose up -d
    ```