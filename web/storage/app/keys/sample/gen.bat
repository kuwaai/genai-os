openssl genpkey -algorithm RSA -out priv.pem -pkeyopt rsa_keygen_bits:2048
openssl rsa -pubout -in priv.pem -out pub.pem