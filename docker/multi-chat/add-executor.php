<?php

# A backdoor API to add executor using FastCGI.
# [Warning] This API should not be exposed to the Internet.

$access_code = escapeshellarg($_POST["access_code"]);
$name = escapeshellarg($_POST["name"]);
$image = "";
if (isset($_POST["image"])){
    $image = '/app/default-icons/' . $_POST["image"];
    $image = "--image=".escapeshellarg($image);
}

exec(sprintf("php artisan model:config %s %s %s 2>&1", $access_code, $name, $image), $output, $retval);
echo "Returned with status $retval and output:\n";
print_r($output);