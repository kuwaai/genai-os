<?php

$proxies = [];

if(env('TRUSTED_PROXIES')){
    foreach (explode(',', env('TRUSTED_PROXIES')) as $ip) {
        if(filter_var($ip, FILTER_VALIDATE_IP)){
            $proxies[] = $ip;
        }
    }
}

return [
    'proxies' => $proxies
];