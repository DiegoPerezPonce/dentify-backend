<?php

$config = [
    "digest_alg" => "sha512",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// Create the private and public key
$res = openssl_pkey_new($config);

// Extract the private key from $res to $privKey
openssl_pkey_export($res, $privKey, "7c6051bc277642d9a0390adf8c7d8802");

// Extract the public key from $res to $pubKey
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];

$dir = __DIR__ . '/config/jwt';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

file_put_contents($dir . '/private.pem', $privKey);
file_put_contents($dir . '/public.pem', $pubKey);

echo "Keys generated successfully in $dir\n";
