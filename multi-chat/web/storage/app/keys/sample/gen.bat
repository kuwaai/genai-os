openssl genpkey -algorithm RSA -out priv.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in priv.pem -out pub.pem