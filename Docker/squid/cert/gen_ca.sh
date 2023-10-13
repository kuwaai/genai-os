#!/bin/bash

openssl req -batch -new -newkey rsa:2048 -days 365 -nodes -x509 -extensions v3_ca -keyout squid-self-signed.key -out squid-self-signed.crt

# Convert the cert into a trusted certificate in DER format.
openssl x509 -in squid-self-signed.crt -outform DER -out squid-self-signed.der

# Convert the cert into a trusted certificate in PEM format.
openssl x509 -in squid-self-signed.crt -outform PEM -out squid-self-signed.pem

# Generate the settings file for the Diffie-Hellman algorithm.
openssl dhparam -outform PEM -out squid-self-signed_dhparam.pem 2048
